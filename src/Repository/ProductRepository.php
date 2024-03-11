<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\QueryBuilder;
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
                ->andWhere('cl.user = :userId OR (pl.userType = :userType AND cl.user IS NULL)')
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
    public function getPaginatedResultsByCategory(Category $category, $page = 1, $limit = 10)
    {
        $query = $this->createQueryBuilder('p') // Use 'p' as the alias for the Product entity
        ->select('p',)
            ->leftJoin('p.productCategories', 'pc')
            ->leftJoin('pc.category', 'c')
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

    /**
     * @throws \Exception
     */
    public function getFilteredResults(array $options)
    {
        $query = $this->createQueryBuilder('p')
            ->select('p', 'CASE
            WHEN (cl.price IS NOT NULL AND :userExists = true) THEN cl.price
            WHEN (pl.price IS NOT NULL AND :userExists = true) THEN pl.price
            ELSE p.price
            END AS real_price')
            ->leftJoin('p.contractLists', 'cl')
            ->leftJoin('p.priceLists', 'pl')
            ->setParameter("userExists", array_key_exists('user_id', $options));

        $this->applyUserFilters($query, $options);
        $this->applyNameFilter($query, $options);
        $this->applyCategoryIdFilter($query, $options);
        $this->applyPriceFilters($query, $options);
        $this->applySorting($query, $options);


        $limit = array_key_exists('limit', $options) ? (int)$options['limit'] : 10;
        $page = array_key_exists('page', $options) ? (int)$options['page'] : 1;
        $paginator = new Paginator($query);
        $paginator->getQuery()
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return $paginator->getIterator()->getArrayCopy();
    }

    public function getProductWithTotalPrice(Product $product, ?User $user)
    {
        return $this->createQueryBuilder('p')
            ->select('p', 'CASE
            WHEN (cl.price IS NOT NULL AND :userExists = true) THEN cl.price
            WHEN (pl.price IS NOT NULL AND :userExists = true) THEN pl.price
            ELSE p.price
            END AS real_price')
            ->leftJoin('p.contractLists', 'cl')
            ->leftJoin('p.priceLists', 'pl')
            ->setParameter('userExists', (boolean)$user)
            ->andWhere('p.sku = :sku')
            ->setParameter('sku', $product->getSku())
            ->getQuery()
            ->getResult();
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
    private function applyUserFilters(QueryBuilder $query, array $options): void
    {
        if (array_key_exists('user_id', $options)) {
            $options['user_id'] = $options['user_id'] !== null ? (int)$options['user_id'] : null;
            $user = $this->userRepository->find($options['user_id']);
            if ($user) {
                $query
                    ->andWhere('(
                    cl.user = :userId OR
                    (pl.userType = :userType AND cl.user IS NULL) OR
                    (cl.id IS NULL AND pl.id IS NULL)
                )')
                    ->setParameter('userId', $user->getId())
                    ->setParameter('userType', $user->getType());
            }
        }
    }

    private function applyNameFilter(QueryBuilder $query, array $options): void
    {
        if (array_key_exists('name', $options)) {
            $query
                ->andWhere('p.name = :name')
                ->setParameter('name', $options['name']);
        }
    }

    private function applyCategoryIdFilter(QueryBuilder $query, array $options): void
    {
        if (array_key_exists('categoryId', $options)) {
            $query
                ->join('p.productCategories', 'pc')
                ->andWhere('pc.category = :categoryId')
                ->setParameter('categoryId', $options['categoryId']);
        }
    }

    private function applyPriceFilters(QueryBuilder $query, array $options): void
    {
        if (array_key_exists('minPrice', $options)) {
            $query
                ->andHaving('real_price > :minPrice')
                ->setParameter('minPrice', (float)$options['minPrice']);
        }

        if (array_key_exists('maxPrice', $options)) {
            $query
                ->andHaving('real_price < :maxPrice')
                ->setParameter('maxPrice', (float)$options['maxPrice']);
        }
    }

    private function applySorting(QueryBuilder $query, array $options): void
    {
        if (array_key_exists('sort', $options)) {
            $query->orderBy('p.' . array_key_first($options['sort']), array_values($options['sort'])[0]);
        }
    }
}
