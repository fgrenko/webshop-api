<?php

namespace App\Entity;

use App\Repository\ContractListRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ContractListRepository::class)]
class ContractList
{
    #[Groups(["contract_list"])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(["contract_list"])]
    #[ORM\ManyToOne(inversedBy: 'contractLists')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[Groups(["contract_list"])]
    #[ORM\ManyToOne(inversedBy: 'contractLists')]
    #[ORM\JoinColumn(referencedColumnName: "sku", nullable: false)]
    private ?Product $product = null;
    #[Groups(["contract_list"])]
    #[ORM\Column(type: 'decimal', scale: 2)]
    private ?float $price = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

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
