<?php

namespace App\Repository;

use App\Entity\PriceList;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Service\Attribute\Required;
use Doctrine\ORM\NoResultException;

/**
 * @extends ServiceEntityRepository<PriceList>
 *
 * @method PriceList|null find($id, $lockMode = null, $lockVersion = null)
 * @method PriceList|null findOneBy(array $criteria, array $orderBy = null)
 * @method PriceList[]    findAll()
 * @method PriceList[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PriceListRepository extends ServiceEntityRepository
{
    #[Required]
    public EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PriceList::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(PriceList $entity, bool $flush = true): void
    {
        $this->entityManager->persist($entity);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(PriceList $entity, bool $flush = true): void
    {
        $this->entityManager->remove($entity);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function findPriceByProductAndType(Product $product, int $userType): ?float
    {
        try {
            $price = $this->createQueryBuilder('pl')
                ->select('pl.price')
                ->where('pl.product = :sku')
                ->andWhere('pl.type = ' . $userType)
                ->setParameter('sku', $product->getSku())
                ->getQuery()
                ->getSingleScalarResult();

            return (float)$price;
        } catch (NoResultException $e) {
            return null;
        }
    }


    //    /**
    //     * @return PriceList[] Returns an array of PriceList objects
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

    //    public function findOneBySomeField($value): ?PriceList
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
