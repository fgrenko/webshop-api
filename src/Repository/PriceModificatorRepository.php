<?php

namespace App\Repository;

use App\Entity\PriceModificator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PriceModificator>
 *
 * @method PriceModificator|null find($id, $lockMode = null, $lockVersion = null)
 * @method PriceModificator|null findOneBy(array $criteria, array $orderBy = null)
 * @method PriceModificator[]    findAll()
 * @method PriceModificator[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PriceModificatorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PriceModificator::class);
    }

    //    /**
    //     * @return PriceModificator[] Returns an array of PriceModificator objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?PriceModificator
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
