<?php

namespace App\Entity;

use App\Repository\ProductCategoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ProductCategoryRepository::class)]
#[UniqueEntity(fields: ['product', 'category'])]
#[ORM\UniqueConstraint(name: "unique_product_category", columns: ['product_id', 'category_id'])]
class ProductCategory
{
    #[Groups(["product_category", "product", "category"])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[Groups(["product_category", "category"])]
    #[ORM\ManyToOne(inversedBy: 'productCategories')]
    #[ORM\JoinColumn(referencedColumnName: "sku", nullable: false)]
    private ?Product $product = null;
    #[Groups(["product_category", "product"])]
    #[ORM\ManyToOne(inversedBy: 'productCategories')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }
}
