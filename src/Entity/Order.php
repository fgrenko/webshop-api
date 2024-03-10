<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{

    #[Groups(["order"])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(["order"])]
    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $buyer = null;

    #[Groups(["order"])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $firstName = null;

    #[Groups(["order"])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lastName = null;

    #[Groups(["order"])]
    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[Groups(["order"])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phoneNumber = null;

    #[Groups(["order"])]
    #[ORM\Column(length: 255)]
    private ?string $address = null;

    #[Groups(["order"])]
    #[ORM\Column(length: 255)]
    private ?string $city = null;

    #[Groups(["order"])]
    #[ORM\Column(length: 255)]
    private ?string $country = null;

    #[Groups(["order"])]
    #[ORM\ManyToMany(targetEntity: PriceModificator::class, inversedBy: 'orders')]
    private Collection $priceModificators;

    #[Groups(["order"])]
    #[ORM\OneToMany(targetEntity: OrderProduct::class, mappedBy: 'orderItem')]
    private Collection $orderProducts;

    #[Groups(["order"])]
    #[ORM\Column(type: 'decimal', scale: 2)]
    private ?float $totalPrice = null;

    public function __construct()
    {
        $this->priceModificators = new ArrayCollection();
        $this->orderProducts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBuyer(): ?User
    {
        return $this->buyer;
    }

    public function setBuyer(?User $buyer): static
    {
        $this->buyer = $buyer;
        $this->setBuyerDetails();

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return Collection<int, PriceModificator>
     */
    public function getPriceModificators(): Collection
    {
        return $this->priceModificators;
    }

    public function addPriceModificator(PriceModificator $priceModificator): static
    {
        if (!$this->priceModificators->contains($priceModificator)) {
            $this->priceModificators->add($priceModificator);
        }

        return $this;
    }

    public function removePriceModificator(PriceModificator $priceModificator): static
    {
        $this->priceModificators->removeElement($priceModificator);

        return $this;
    }

    /**
     * @return Collection<int, OrderProduct>
     */
    public function getOrderProducts(): Collection
    {
        return $this->orderProducts;
    }

    public function addOrderProduct(OrderProduct $orderProduct): static
    {
        if (!$this->orderProducts->contains($orderProduct)) {
            $this->orderProducts->add($orderProduct);
            $orderProduct->setOrderItem($this);
        }

        return $this;
    }

    public function removeOrderProduct(OrderProduct $orderProduct): static
    {
        if ($this->orderProducts->removeElement($orderProduct)) {
            // set the owning side to null (unless already changed)
            if ($orderProduct->getOrderItem() === $this) {
                $orderProduct->setOrderItem(null);
            }
        }

        return $this;
    }

    private function setBuyerDetails(): void
    {
        $this->setFirstName($this->buyer->getFirstName());
        $this->setLastName($this->buyer->getLastName());
        $this->setEmail($this->buyer->getEmail());
        $this->setPhoneNumber($this->buyer->getPhoneNumber());
        $this->setCity($this->buyer->getCity());
        $this->setAddress($this->buyer->getAddress());
        $this->setCountry($this->buyer->getCountry());
    }

    public function calculateTotalPrice(array $priceModificators): float
    {
        $totalPrice = 0;

        // Loop through products and sum their price
        /**
         * @var $orderProduct OrderProduct
         */
        foreach ($this->orderProducts as $orderProduct) {

            $totalPrice += ($orderProduct->getPrice() * $orderProduct->getQuantity());
        }

        // Calculate price modificators
        /**
         * @var $priceModificator PriceModificator
         */
        $priceModificatorValues = [];
        foreach ($priceModificators as $priceModificator) {
            $amount = ($priceModificator->getPercentage() / 100) * $totalPrice;
            if ($priceModificator->getType() == TYPE_DISCOUNT) {
                $amount *= -1;
            }
            $priceModificatorValues[] = $amount;
        }
        $totalPrice += array_sum($priceModificatorValues);

        if ($totalPrice > 100) {
            foreach ($priceModificators as $priceModificator) {
                $this->addPriceModificator($priceModificator);
            }
        }

        return $totalPrice;
    }

    public function getTotalPrice(): ?float
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(array $priceModificators): static
    {
        $this->totalPrice = $this->calculateTotalPrice($priceModificators);

        return $this;
    }


}
