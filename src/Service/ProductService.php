<?php

namespace App\Service;

use App\Entity\Product;
use App\Repository\PriceListRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Contracts\Service\Attribute\Required;

class ProductService
{
    #[Required]
    public ProductRepository $productRepository;
    #[Required]
    public PriceListRepository $priceListRepository;
    #[Required]
    public UserRepository $userRepository;

    function getPaginatedResults(int $userId, $page = 1, $limit = 10)
    {
        $user = $this->userRepository->find($userId);
        $query = $this->productRepository->createQueryBuilder('p')
            ->select('p', 'pl') // Select both Product and PriceList
            ->leftJoin('p.priceLists', 'pl') // Left join PriceList entity
            ->getQuery();

        $paginator = new Paginator($query);
        $paginator->getQuery()
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $products = $paginator->getIterator()->getArrayCopy();
        foreach ($products as &$product) {
            $product->setPrice($this->productRepository->findPriceForUser($product, $user));
        }

        return $products;
    }
}
