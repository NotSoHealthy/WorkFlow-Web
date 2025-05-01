<?php

namespace App\Repository;

use App\Entity\Reclamation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reclamation>
 */
class ReclamationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reclamation::class);
    }

  /**
     * Find reclamations with optional search, sort, and status filters.
     *
     * @param string|null $searchTerm Search term for title or description
     * @param string|null $sort Sort by date ('newest' or 'oldest')
     * @param string|null $status Filter by status (e.g., 'Ouvert', 'En cours', etc.)
     * @return Reclamation[]
     */
    public function findFilteredAndSorted(?string $searchTerm = null, ?string $sort = 'newest', ?string $status = null): array
    {
        $qb = $this->createQueryBuilder('r');

        // Search filter
        if ($searchTerm) {
            $qb->andWhere('LOWER(r.titre) LIKE :search OR LOWER(r.description) LIKE :search')
               ->setParameter('search', '%' . strtolower($searchTerm) . '%');
        }

        // Status filter
        if ($status && $status !== '') {
            $qb->andWhere('r.etat = :status')
               ->setParameter('status', $status);
        }

        // Sort by date
        $qb->orderBy('r.date', $sort === 'newest' ? 'DESC' : 'ASC');

        return $qb->getQuery()->getResult();
    }
}
