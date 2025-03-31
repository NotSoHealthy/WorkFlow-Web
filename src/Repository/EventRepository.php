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
}
