<?php

namespace App\Entity;

use App\Repository\FileRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FileRepository::class)]
class File
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $alt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 30)]
    private ?string $mime_type = null;

    #[ORM\Column(length: 30)]
    private ?string $source = null;

    #[ORM\Column(length: 255)]
    private ?string $file_name = null;

    #[ORM\Column(length: 10)]
    private ?string $extension = null;

    #[ORM\Column]
    private ?int $file_size = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cdn_host = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cdn_account = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cdn_server = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAlt(): ?string
    {
        return $this->alt;
    }

    public function setAlt(?string $alt): self
    {
        $this->alt = $alt;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mime_type;
    }

    public function setMimeType(string $mime_type): self
    {
        $this->mime_type = $mime_type;

        return $this;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(string $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->file_name;
    }

    public function setFileName(string $file_name): self
    {
        $this->file_name = $file_name;

        return $this;
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function setExtension(string $extension): self
    {
        $this->extension = $extension;

        return $this;
    }

    public function getFileSize(): ?int
    {
        return $this->file_size;
    }

    public function setFileSize(int $file_size): self
    {
        $this->file_size = $file_size;

        return $this;
    }

    public function getCdnHost(): ?string
    {
        return $this->cdn_host;
    }

    public function setCdnHost(?string $cdn_host): self
    {
        $this->cdn_host = $cdn_host;

        return $this;
    }

    public function getCdnAccount(): ?string
    {
        return $this->cdn_account;
    }

    public function setCdnAccount(?string $cdn_account): self
    {
        $this->cdn_account = $cdn_account;

        return $this;
    }

    public function getCdnServer(): ?string
    {
        return $this->cdn_server;
    }

    public function setCdnServer(?string $cdn_server): self
    {
        $this->cdn_server = $cdn_server;

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

}
