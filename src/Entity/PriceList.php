<?php

namespace App\Entity;

use App\Repository\PriceListRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: PriceListRepository::class)]
#[UniqueEntity(fields: ['product', 'userType'])]
#[ORM\UniqueConstraint(name: "unique_price_list", columns: ['product_id', 'user_type'])]
class PriceList
{
    #[Groups(["price_list"])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[Groups(["price_list"])]
    #[ORM\Column(length: 255)]
    private ?string $name = null;
    #[Groups(["price_list"])]
    #[ORM\Column(type: 'decimal', scale: 2)]
    private ?float $price = null;
    #[Groups(["price_list"])]
    #[ORM\ManyToOne(inversedBy: 'priceLists')]
    #[ORM\JoinColumn(referencedColumnName: "sku", nullable: false)]
    private ?Product $product = null;

    #[Groups(["price_list"])]
    #[ORM\Column]
    private ?int $userType = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
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

    public function getUserType(): ?int
    {
        return $this->userType;
    }

    public function setUserType(int $userType): static
    {
        $this->userType = $userType;

        return $this;
    }
}
