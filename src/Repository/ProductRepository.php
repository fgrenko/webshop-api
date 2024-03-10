<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    #[Required]
    public EntityManagerInterface $entityManager;
    #[Required]
    public UserRepository $userRepository;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Product $entity, bool $flush = true): void
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
    public function remove(Product $entity, bool $flush = true): void
    {
        $this->entityManager->remove($entity);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function findPriceForUser(Product $product, User $user = null): ?float
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->select('CASE
            WHEN cl.price IS NOT NULL THEN cl.price
            WHEN pl.price IS NOT NULL THEN pl.price
            ELSE p.price
            END AS final_price')
            ->leftJoin('p.contractLists', 'cl')
            ->leftJoin('p.priceLists', 'pl')
            ->andWhere('p.sku = :sku')
            ->setParameter('sku', $product->getSku());

        if ($user) {
            $queryBuilder
                ->andWhere('cl.user = :userId OR (pl.type = :userType AND cl.user IS NULL)')
                ->setParameter('userId', $user->getId())
                ->setParameter('userType', $user->getType());
        }

        $result = $queryBuilder->getQuery()->getOneOrNullResult();

        if ($result !== null) {
            return (float)$result['final_price'];
        }

        return $product->getPrice();
    }

    /**
     * @throws \Exception
     */
    public function getPaginatedResults(int $userId = null, $page = 1, $limit = 10)
    {
        $user = null;
        if ($userId) {
            $user = $this->userRepository->find($userId);
        }
        $query = $this->createQueryBuilder('p')
            ->select('p', 'pl') // Select both Product and PriceList
            ->leftJoin('p.priceLists', 'pl') // Left join PriceList entity
            ->getQuery();

        $paginator = new Paginator($query);
        $paginator->getQuery()
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $products = $paginator->getIterator()->getArrayCopy();
        foreach ($products as &$product) {
            $product->setPrice($this->findPriceForUser($product, $user));
        }

        return $products;
    }

    public function getPaginatedResultsByCategory(Category $category, $page = 1, $limit = 10)
    {
        $query = $this->createQueryBuilder('p') // Use 'p' as the alias for the Product entity
        ->select('p')
            ->leftJoin('p.productCategories', 'pc') // Assuming 'productCategories' is the property representing the relationship between Product and ProductCategory
            ->leftJoin('pc.category', 'c') // Assuming 'category' is the property representing the relationship between ProductCategory and Category
            ->andWhere('c = :category')
            ->setParameter('category', $category)
            ->getQuery();

        $paginator = new Paginator($query);
        $paginator->getQuery()
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $products = $paginator->getIterator()->getArrayCopy();

        return $products;
    }


    //    /**
    //     * @return Product[] Returns an array of Product objects
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

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
