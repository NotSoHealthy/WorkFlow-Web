<?php

namespace App\Repository;

use App\Entity\Inscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Inscription>
 */
class InscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Inscription::class);
    }
    public function sortInscriptions(string $sortBy): array
    {
       $queryBuilder = $this->createQueryBuilder('i');

       if ($sortBy === 'approuver' || $sortBy === 'en attente') {
            $queryBuilder
            ->where('i.status = :status')
            ->setParameter('status', $sortBy);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function getInscriptionsCountPerFormation(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT f.title as formation_title, COUNT(i.id) as count FROM inscription i INNER JOIN formation f ON i.formation = f.id WHERE i.status = 'approuver' GROUP BY f.title ORDER BY count DESC ";
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery()->fetchAllAssociative();

        $stats = [];
        foreach ($result as $row) {
            $stats[$row['formation_title']] = $row['count'];
        }

        return $stats;
    }

    //    /**
    //     * @return Inscription[] Returns an array of Inscription objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('i.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Inscription
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
