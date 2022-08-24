<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'products')]
    private ?self $parent = null;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class)]
    private Collection $products;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: Price::class)]
    private Collection $price;

    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?Category $category = null;

    #[ORM\Column]
    private ?int $stock = null;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: Stock::class, orphanRemoval: true)]
    private Collection $stocks;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 80)]
    private ?string $name = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $sku = null;

    #[ORM\Column(length: 13, nullable: true)]
    private ?string $ean = null;

    #[ORM\Column(length: 14,  nullable: true)]
    private ?string $gtin = null;

    #[ORM\Column(length: 14,  nullable: true)]
    private ?string $isbn = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $manufacturer_code = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $model_number = null;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: PropertyValue::class, orphanRemoval: true)]
    private Collection $propertyValues;

    #[ORM\Column(nullable: true)]
    private ?array $additional_properties = [];

    #[ORM\Column(nullable: true)]
    private ?array $highlights = [];

    #[ORM\ManyToMany(targetEntity: File::class)]
    private Collection $file;

    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->price = new ArrayCollection();
        $this->propertyValues = new ArrayCollection();
        $this->stocks = new ArrayCollection();
        $this->file = new ArrayCollection();
    }

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

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(self $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->setParent($this);
        }

        return $this;
    }

    public function removeProduct(self $product): self
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getParent() === $this) {
                $product->setParent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Price>
     */
    public function getPrice(): Collection
    {
        return $this->price;
    }

    public function addPrice(Price $price): self
    {
        if (!$this->price->contains($price)) {
            $this->price->add($price);
            $price->setProduct($this);
        }

        return $this;
    }

    public function removePrice(Price $price): self
    {
        if ($this->price->removeElement($price)) {
            // set the owning side to null (unless already changed)
            if ($price->getProduct() === $this) {
                $price->setProduct(null);
            }
        }

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): self
    {
        $this->stock = $stock;

        return $this;
    }

    /**
     * @return Collection<int, Stock>
     */
    public function getStocks(): Collection
    {
        return $this->stocks;
    }

    public function addStock(Stock $stock): self
    {
        if (!$this->stocks->contains($stock)) {
            $this->stocks->add($stock);
            $stock->setProduct($this);
        }

        return $this;
    }

    public function removeStock(Stock $stock): self
    {
        if ($this->stocks->removeElement($stock)) {
            // set the owning side to null (unless already changed)
            if ($stock->getProduct() === $this) {
                $stock->setProduct(null);
            }
        }

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
        if($this->ean > 0) {
            return str_pad($this->ean, 13, 0, STR_PAD_LEFT);
        }
        
        return $this->ean;
    }

    public function setEan(?string $ean): self
    {

        $ean = preg_replace('~\D~', '', $ean);

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

    public function getManufacturerCode(): ?string
    {
        return $this->manufacturer_code;
    }

    public function setManufacturerCode(?string $manufacturer_code): self
    {
        $this->manufacturer_code = $manufacturer_code;

        return $this;
    }

    public function getModelNumber(): ?string
    {
        return $this->model_number;
    }

    public function setModelNumber(?string $model_number): self
    {
        $this->model_number = $model_number;

        return $this;
    }

    /**
     * @return Collection<int, PropertyValue>
     */
    public function getPropertyValues(): Collection
    {
        return $this->propertyValues;
    }

    public function addPropertyValue(PropertyValue $propertyValue): self
    {
        if (!$this->propertyValues->contains($propertyValue)) {
            $this->propertyValues->add($propertyValue);
            $propertyValue->setProduct($this);
        }

        return $this;
    }

    public function removePropertyValue(PropertyValue $propertyValue): self
    {
        if ($this->propertyValues->removeElement($propertyValue)) {
            // set the owning side to null (unless already changed)
            if ($propertyValue->getProduct() === $this) {
                $propertyValue->setProduct(null);
            }
        }

        return $this;
    }

    public function getAdditionalProperties(): ?array
    {
        return $this->additional_properties;
    }

    public function setAdditionalProperties(?array $additional_properties): self
    {
        $this->additional_properties = $additional_properties;

        return $this;
    }

    public function getHighlights(): ?array
    {
        return $this->highlights;
    }

    public function setHighlights(?array $highlights): self
    {
        $this->highlights = $highlights;

        return $this;
    }

    /**
     * @return Collection<int, File>
     */
    public function getFile(): Collection
    {
        return $this->file;
    }

    public function addFile(File $file): self
    {
        if (!$this->file->contains($file)) {
            $this->file->add($file);
        }

        return $this;
    }

    public function removeFile(File $file): self
    {
        $this->file->removeElement($file);

        return $this;
    }
    
}
