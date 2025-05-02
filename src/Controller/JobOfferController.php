<?php

namespace App\Controller;

use App\Entity\JobOffer;
use App\Form\JobOfferType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use GuzzleHttp\Client;

class JobOfferController extends AbstractController
{
    private EntityManagerInterface $em;
    private LoggerInterface $logger;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->em     = $em;
        $this->logger = $logger;
    }

    #[Route('/joboffer', name: 'job_offer_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $search = $request->query->get('search', '');
        $sort   = $request->query->get('sort', 'newest');

        $qb = $this->em->getRepository(JobOffer::class)->createQueryBuilder('jo');

        if ($search) {
            $qb->andWhere('jo.Title LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $qb->orderBy('jo.Publication_Date', $sort === 'newest' ? 'DESC' : 'ASC');

        $jobOffers = $qb->getQuery()->getResult();

        if ($request->isXmlHttpRequest()) {
            return $this->render('job_offer/_list.html.twig', [
                'jobOffers' => $jobOffers,
            ]);
        }

        return $this->render('job_offer/index.html.twig', [
            'jobOffers' => $jobOffers,
        ]);
    }

    #[Route('/job-offer/wizard/{id?}', name: 'job_offer_wizard', methods: ['GET'])]
    public function wizard(Request $request, JobOffer $jobOffer = null): Response
    {
        $jobOffer ??= new JobOffer();
        $form = $this->createForm(JobOfferType::class, $jobOffer);

        return $this->render('job_offer/wizard.html.twig', [
            'form'     => $form->createView(),
            'jobOffer' => $jobOffer,
        ]);
    }

    #[Route('/job-offer/create', name: 'job_offer_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $jobOffer = new JobOffer();
        $form     = $this->createForm(JobOfferType::class, $jobOffer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$jobOffer->getPublicationDate()) {
                $jobOffer->setPublicationDate(new \DateTime());
            }

            $jobOffer->setUser($this->getUser());
            $this->em->persist($jobOffer);
            $this->em->flush();

            // Post to Mastodon
            $socialResult = 'Not attempted';
            try {
                $client = new Client();
                $text = sprintf(
                    "Nouvelle offre : %s\n%s\nContrat : %s\nSalaire : %s TND\nPublié le : %s #JobOffer",
                    $jobOffer->getTitle(),
                    mb_strimwidth(strip_tags($jobOffer->getDescription()), 0, 400, '…'),
                    $jobOffer->getContractType(),
                    $jobOffer->getSalary(),
                    $jobOffer->getPublicationDate()->format('Y-m-d')
                );

                if (strlen($text) > 500) {
                    $text = substr($text, 0, 497) . '...';
                }

                $this->logger->debug('Posting to Mastodon', ['text' => $text]);

                $response = $client->post($_ENV['MASTODON_INSTANCE'] . '/api/v1/statuses', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $_ENV['MASTODON_ACCESS_TOKEN'],
                        'Content-Type' => 'application/json'
                    ],
                    'json' => [
                        'status' => $text,
                        'visibility' => 'public' // Options: public, unlisted, private, direct
                    ]
                ]);

                $result = json_decode($response->getBody()->getContents(), true);

                $this->logger->info('Mastodon API response', [
                    'status' => $response->getStatusCode(),
                    'body' => json_encode($result)
                ]);

                if (isset($result['id'])) {
                    $socialResult = 'Toot posted successfully';
                    $this->logger->info('Toot sent successfully', ['toot_id' => $result['id']]);
                } else {
                    $socialResult = 'Mastodon error: ' . json_encode($result);
                    $this->logger->error('Mastodon API error', ['response' => json_encode($result)]);
                }
            } catch (\Exception $e) {
                $socialResult = 'Exception: ' . $e->getMessage();
                $this->logger->error('Mastodon exception', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }

            return new JsonResponse([
                'success' => true,
                'twitter' => $socialResult, 
                'job'     => [
                    'id'              => $jobOffer->getId(),
                    'title'           => $jobOffer->getTitle(),
                    'description'     => $jobOffer->getDescription(),
                    'publicationDate' => $jobOffer->getPublicationDate()->format('Y-m-d'),
                    'expirationDate'  => $jobOffer->getExpirationDate()?->format('Y-m-d') ?? '',
                    'contractType'    => $jobOffer->getContractType(),
                    'salary'          => $jobOffer->getSalary(),
                ],
            ]);
        }

        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }

        return new JsonResponse([
            'success' => false,
            'message' => implode("\n", $errors),
            'twitter' => 'Not attempted',
        ], 400);
    }

    #[Route('/job-offer/update/{id}', name: 'job_offer_update', methods: ['POST'])]
    public function update(Request $request, JobOffer $jobOffer): JsonResponse
    {
        $form = $this->createForm(JobOfferType::class, $jobOffer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$jobOffer->getPublicationDate()) {
                $jobOffer->setPublicationDate(new \DateTime());
            }

            $jobOffer->setUser($this->getUser());
            $this->em->persist($jobOffer);
            $this->em->flush();

            return new JsonResponse([
                'success' => true,
                'twitter' => true,
                'job'     => [
                    'id'              => $jobOffer->getId(),
                    'title'           => $jobOffer->getTitle(),
                    'description'     => $jobOffer->getDescription(),
                    'publicationDate' => $jobOffer->getPublicationDate()->format('Y-m-d'),
                    'expirationDate'  => $jobOffer->getExpirationDate()?->format('Y-m-d') ?? '',
                    'contractType'    => $jobOffer->getContractType(),
                    'salary'          => $jobOffer->getSalary(),
                ],
            ]);
        }

        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }

        return new JsonResponse([
            'success' => false,
            'message' => implode("\n", $errors),
            'twitter' => false,
        ], 400);
    }

    #[Route('/job-offer/delete/{id}', name: 'job_offer_delete', methods: ['DELETE'])]
    public function delete(JobOffer $jobOffer): JsonResponse
    {
        $this->em->remove($jobOffer);
        $this->em->flush();
        return new JsonResponse(['success' => true]);
    }
}
