<?php

namespace App\Service;

use App\Entity\Product;
use App\Repository\PriceListRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Contracts\Service\Attribute\Required;

class ProductService
{
    #[Required]
    public ProductRepository $productRepository;
    #[Required]
    public PriceListRepository $priceListRepository;

    function getPaginatedResults(int $userType, $page = 1, $limit = 10)
    {
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
            $this->alterPrice($product, $userType);
        }

        return $products;
    }

    function alterPrice(Product $product, int $userType): void
    {
        $price = $this->priceListRepository->findPriceByProductAndType($product, $userType);
        if ($price != null) {
            $product->setPrice($price);
        }
    }
}
