<?php

namespace App\Entity;

use App\Repository\PriceModificatorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;

const TYPE_VAT = 0;
const TYPE_DISCOUNT = 1;
#[ORM\Entity(repositoryClass: PriceModificatorRepository::class)]
#[UniqueEntity(fields: ['name'])]
#[ORM\UniqueConstraint(name: "unique_product_category", columns: ['name'])]
class PriceModificator
{
    #[Groups(["order"])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(["order"])]
    #[ORM\Column]
    private ?int $type = null;

    #[Groups(["order"])]
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[Groups(["order"])]
    #[ORM\Column]
    private ?float $percentage = null;

    #[Groups(["order"])]
    #[ORM\Column]
    private ?bool $active = true;

    #[ORM\ManyToMany(targetEntity: Order::class, mappedBy: 'priceModificators')]
    private Collection $orders;

    public function __construct()
    {
        $this->orders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): static
    {
        $this->type = $type;

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

    public function getPercentage(): ?float
    {
        return $this->percentage;
    }

    public function setPercentage(float $percentage): static
    {
        $this->percentage = $percentage;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->addPriceModificator($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            $order->removePriceModificator($this);
        }

        return $this;
    }
}
