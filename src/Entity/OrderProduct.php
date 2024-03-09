<?php

namespace App\Entity;

use App\Repository\OrderProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: OrderProductRepository::class)]
#[UniqueEntity(fields: ['product', 'orderItem'])]
#[ORM\UniqueConstraint(name: "unique_order_product", columns: ['product_id', 'order_item_id'])]
class OrderProduct
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(["order"])]
    #[ORM\ManyToOne(inversedBy: 'orderProducts')]
    #[ORM\JoinColumn(referencedColumnName: "sku", nullable: false)]
    private ?Product $product = null;

    #[Groups(["order"])]
    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\ManyToOne(inversedBy: 'orderProducts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Order $orderItem = null;

    #[Groups(["order"])]
    #[ORM\Column]
    private ?float $price = null;

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

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getOrderItem(): ?Order
    {
        return $this->orderItem;
    }

    public function setOrderItem(?Order $orderItem): static
    {
        $this->orderItem = $orderItem;

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
}
