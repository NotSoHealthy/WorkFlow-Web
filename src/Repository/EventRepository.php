<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function increaseNumberOfPlaces(int $eventId, int $increment): int
    {
        return $this->createQueryBuilder('e')
            ->update()
            ->set('e.NumberOfPlaces', 'e.NumberOfPlaces + :increment')
            ->where('e.id = :id')
            ->setParameter('increment', $increment)
            ->setParameter('id', $eventId)
            ->getQuery()
            ->execute();
    }
    public function decreaseNumberOfPlaces(int $eventId, int $decrement): int
    {
        $event = $this->find($eventId);
        if (!$event) {
            throw new \InvalidArgumentException("Event not found for id {$eventId}");
        }
        $current = $event->getNumberOfPlaces();
        $newNumber = max(0, $current - $decrement);
        $event->setNumberOfPlaces($newNumber);
        $this->getEntityManager()->flush();
        return 1;
    }
    public function findByTitle(string $title): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.Title LIKE :title')
            ->setParameter('title', '%' . $title . '%')
            ->getQuery()
            ->getResult();
    }
    public function findBySearchQuery(?string $query, ?string $sortByDate, ?string $eventType)
    {
        $qb = $this->createQueryBuilder('e');

        if ($query) {
            $qb->andWhere('e.Title LIKE :query')
               ->setParameter('query', '%' . $query . '%');
        }

        if ($eventType) {
            $qb->andWhere('e.Type = :eventType')
               ->setParameter('eventType', $eventType);
        }

        if ($sortByDate) {
            $qb->orderBy('e.DateAndTime', 'ASC');
        }

        return $qb->getQuery()->getResult();
    }

    public function countByType(string $type): int
    {
        return $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.Type = :type')
            ->setParameter('type', $type)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findMostPopularEvent(): ?array
    {
        $entityManager = $this->getEntityManager();
        
        return $entityManager->createQuery(
            'SELECT e.Title,e.id, COUNT(r.id) as reservationCount
             FROM App\Entity\Event e
             JOIN e.reservations r
             GROUP BY e.id
             ORDER BY reservationCount DESC'
        )
        ->setMaxResults(1)
        ->getOneOrNullResult();
    }

    public function findClosestUpcomingEvent(): ?Event
    {
        return $this->createQueryBuilder('e')
            ->where('e.DateAndTime > :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('e.DateAndTime', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
