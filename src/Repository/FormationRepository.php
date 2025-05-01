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
    public function searchAndSortFormations(string $search, string $sort): array
    {
        $qb = $this->createQueryBuilder('f');

        if ($search) {
            $qb->where('f.title LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($sort === 'titre') {
            $qb->orderBy('f.title', 'ASC');
        } 
        elseif ($sort === 'period') {
            $qb->orderBy('f.date_begin', 'ASC')
                ->addOrderBy('f.date_end', 'ASC');
        }

        return $qb->getQuery()->getResult();
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
