<?php

namespace App\Repository;

use App\Entity\Formation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
/**
 * @extends ServiceEntityRepository<Formation>
 */
class FormationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Formation::class);
    }
    public function searchFormations(string $search): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.title LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('f.date_begin', 'ASC')
            ->getQuery()
            ->getResult();
    }
    public function sortFormations(string $sortBy): array
    {
        $queryBuilder = $this->createQueryBuilder('f');

        if ($sortBy === 'titre') {
            $queryBuilder->orderBy('f.title', "ASC");
        } 
        elseif ($sortBy === 'period') {
       
            $queryBuilder->orderBy('f.date_begin', "ASC"); 
            $queryBuilder->addOrderBy('f.date_end', "ASC"); 
    
        }

        return $queryBuilder->getQuery()->getResult();
    }
    public function getFormationsCountPerYear(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT YEAR(f.date_begin) as year, COUNT(f.id) as count FROM formation f GROUP BY year ORDER BY year ASC';
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery()->fetchAllAssociative();
        $stats = [];
        foreach ($result as $row) {
            $stats[$row['year']] = $row['count'];
        }
    
        return $stats;
    }

    //    /**
    //     * @return Formation[] Returns an array of Formation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Formation
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
