<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[UniqueEntity("sku")]
class Product
{
    #[Groups(["product_category", "product", "category", "price_list"])]
    #[ORM\Id]
    #[ORM\Column(length: 64)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    private ?string $sku = null;
    #[Groups(["product_category", "product", "category", "price_list"])]
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $name = null;
    #[Groups(["product_category", "product", "category", "price_list"])]
    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $description = null;
    #[Groups(["product_category", "product", "category", "price_list"])]
    #[ORM\Column]
    #[Assert\NotBlank]
    private ?float $price = null;
    #[Groups(["product_category", "product", "category", "price_list"])]
    #[ORM\Column]
    #[Assert\NotBlank]
    private ?bool $published = null;

    #[Groups(["product_category", "product"])]
    #[ORM\OneToMany(targetEntity: ProductCategory::class, mappedBy: 'product', orphanRemoval: true)]
    private Collection $productCategories;

    #[ORM\OneToMany(targetEntity: PriceList::class, mappedBy: 'product', orphanRemoval: true)]
    private Collection $priceLists;

    public function __construct()
    {
        $this->productCategories = new ArrayCollection();
        $this->priceLists = new ArrayCollection();
    }

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(string $sku): self
    {
        $this->sku = $sku;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function isPublished(): ?bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): static
    {
        $this->published = $published;

        return $this;
    }

    /**
     * @return Collection<int, ProductCategory>
     */
    public function getProductCategories(): Collection
    {
        return $this->productCategories;
    }

    public function addProductCategory(ProductCategory $productCategory): static
    {
        if (!$this->productCategories->contains($productCategory)) {
            $this->productCategories->add($productCategory);
            $productCategory->setProduct($this);
        }

        return $this;
    }

    public function removeProductCategory(ProductCategory $productCategory): static
    {
        if ($this->productCategories->removeElement($productCategory)) {
            // set the owning side to null (unless already changed)
            if ($productCategory->getProduct() === $this) {
                $productCategory->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PriceList>
     */
    public function getPriceLists(): Collection
    {
        return $this->priceLists;
    }

    public function addPriceList(PriceList $priceList): static
    {
        if (!$this->priceLists->contains($priceList)) {
            $this->priceLists->add($priceList);
            $priceList->setProduct($this);
        }

        return $this;
    }

    public function removePriceList(PriceList $priceList): static
    {
        if ($this->priceLists->removeElement($priceList)) {
            // set the owning side to null (unless already changed)
            if ($priceList->getProduct() === $this) {
                $priceList->setProduct(null);
            }
        }

        return $this;
    }
}
