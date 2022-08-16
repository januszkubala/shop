<?php

namespace App\Entity;

use App\Repository\OrderComponentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderComponentRepository::class)]
class OrderComponent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\ManyToOne(inversedBy: 'orderComponents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Order $parent = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 14, scale: 2)]
    private ?string $net_price = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 14, scale: 2)]
    private ?string $price = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 14, scale: 2)]
    private ?string $tax_amount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    private ?string $tax_rate = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $tax_name = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 14, scale: 2)]
    private ?string $net_amount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 14, scale: 2)]
    private ?string $amount = null;

    #[ORM\Column(length: 80)]
    private ?string $name = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $sku = null;

    #[ORM\Column(length: 13, nullable: true)]
    private ?string $ean = null;

    #[ORM\Column(length: 14, nullable: true)]
    private ?string $gtin = null;

    #[ORM\Column(length: 14, nullable: true)]
    private ?string $isbn = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getParent(): ?Order
    {
        return $this->parent;
    }

    public function setParent(?Order $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getNetPrice(): ?string
    {
        return $this->net_price;
    }

    public function setNetPrice(string $net_price): self
    {
        $this->net_price = $net_price;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getTaxAmount(): ?string
    {
        return $this->tax_amount;
    }

    public function setTaxAmount(string $tax_amount): self
    {
        $this->tax_amount = $tax_amount;

        return $this;
    }

    public function getTaxRate(): ?string
    {
        return $this->tax_rate;
    }

    public function setTaxRate(string $tax_rate): self
    {
        $this->tax_rate = $tax_rate;

        return $this;
    }

    public function getTaxName(): ?string
    {
        return $this->tax_name;
    }

    public function setTaxName(?string $tax_name): self
    {
        $this->tax_name = $tax_name;

        return $this;
    }

    public function getNetAmount(): ?string
    {
        return $this->net_amount;
    }

    public function setNetAmount(string $net_amount): self
    {
        $this->net_amount = $net_amount;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(?string $sku): self
    {
        $this->sku = $sku;

        return $this;
    }

    public function getEan(): ?string
    {
        return $this->ean;
    }

    public function setEan(?string $ean): self
    {
        $this->ean = $ean;

        return $this;
    }

    public function getGtin(): ?string
    {
        return $this->gtin;
    }

    public function setGtin(?string $gtin): self
    {
        $this->gtin = $gtin;

        return $this;
    }

    public function getIsbn(): ?string
    {
        return $this->isbn;
    }

    public function setIsbn(?string $isbn): self
    {
        $this->isbn = $isbn;

        return $this;
    }
}
