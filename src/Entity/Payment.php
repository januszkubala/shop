<?php

namespace App\Entity;

use App\Repository\PaymentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
class Payment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'payments')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'payments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Order $allocation = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 14, scale: 2)]
    private ?string $amount = null;

    #[ORM\Column(length: 36)]
    private ?string $ref = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $system_ref = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $card_brand = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $card_expiry_date = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $card_number = null;

    #[ORM\Column(length: 30)]
    private ?string $method = null;

    #[ORM\Column(length: 30)]
    private ?string $status = null;

    #[ORM\Column]
    private ?bool $isAutomatic = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_completed = null;

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

    public function getAllocation(): ?Order
    {
        return $this->allocation;
    }

    public function setAllocation(?Order $allocation): self
    {
        $this->allocation = $allocation;

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

    public function getRef(): ?string
    {
        return $this->ref;
    }

    public function setRef(string $ref): self
    {
        $this->ref = $ref;

        return $this;
    }

    public function getSystemRef(): ?string
    {
        return $this->system_ref;
    }

    public function setSystemRef(?string $system_ref): self
    {
        $this->system_ref = $system_ref;

        return $this;
    }

    public function getCardBrand(): ?string
    {
        return $this->card_brand;
    }

    public function setCardBrand(string $card_brand): self
    {
        $this->card_brand = $card_brand;

        return $this;
    }

    public function getCardExpiryDate(): ?\DateTimeInterface
    {
        return $this->card_expiry_date;
    }

    public function setCardExpiryDate(?\DateTimeInterface $card_expiry_date): self
    {
        $this->card_expiry_date = $card_expiry_date;

        return $this;
    }

    public function getCardNumber(): ?string
    {
        return $this->card_number;
    }

    public function setCardNumber(?string $card_number): self
    {
        $this->card_number = $card_number;

        return $this;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function setMethod(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function isIsAutomatic(): ?bool
    {
        return $this->isAutomatic;
    }

    public function setIsAutomatic(bool $isAutomatic): self
    {
        $this->isAutomatic = $isAutomatic;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getDateCompleted(): ?\DateTimeInterface
    {
        return $this->date_completed;
    }

    public function setDateCompleted(?\DateTimeInterface $date_completed): self
    {
        $this->date_completed = $date_completed;

        return $this;
    }
}
