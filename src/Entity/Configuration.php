<?php

namespace App\Entity;

use App\Repository\ConfigurationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConfigurationRepository::class)]
class Configuration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 16, nullable: true)]
    private ?string $locale = null;

    #[ORM\Column(length: 3)]
    private ?string $currency = null;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $configuration_name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $configuration_description = null;

    #[ORM\Column]
    private ?bool $is_current = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getConfigurationName(): ?string
    {
        return $this->configuration_name;
    }

    public function setConfigurationName(?string $configuration_name): self
    {
        $this->configuration_name = $configuration_name;

        return $this;
    }

    public function getConfigurationDescription(): ?string
    {
        return $this->configuration_description;
    }

    public function setConfigurationDescription(?string $configuration_description): self
    {
        $this->configuration_description = $configuration_description;

        return $this;
    }

    public function isIsCurrent(): ?bool
    {
        return $this->is_current;
    }

    public function setIsCurrent(bool $is_current): self
    {
        $this->is_current = $is_current;

        return $this;
    }
}
