<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Common\Filter\SearchFilterInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[
    ApiResource(
        denormalizationContext: ['groups' => ['product.write']],
        normalizationContext: ['groups' => ['product.read']]
    ),
    ApiFilter(
        SearchFilter::class,
        properties: [
            'name' => SearchFilterInterface::STRATEGY_PARTIAL,
            'description' => SearchFilterInterface::STRATEGY_PARTIAL,
            'manufacturer.countryCode' => SearchFilterInterface::STRATEGY_EXACT,
            'manufacturer.id' => SearchFilterInterface::STRATEGY_EXACT
        ]
    ),
    ApiFilter(
        OrderFilter::class,
        properties: [
            'issueDate'
        ]
    )
]
class Product
{
    /** The id of the product */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** The name of the product */
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['product.read', 'product.write'])]
    private ?string $name = null;

    /** The description of the product */
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['product.read', 'product.write'])]
    private ?string $description = null;

    /** The date of issue of the product */
    #[ORM\Column]
    #[Assert\NotNull]
    #[Groups(['product.read'])]
    private ?\DateTimeImmutable $issueDate = null;

    /** The MPN (manufacturer part number) of the product */
    #[ORM\Column(length: 255)]
    #[Assert\NotNull]
    #[Groups(['product.read', 'product.write'])]
    private ?string $mpn = null;

    /** The manufacturer of the product */
    #[ORM\ManyToOne(inversedBy: 'products')]
    #[Groups(['product.read'])]
    private ?Manufacturer $manufacturer = null;


    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getIssueDate(): ?\DateTimeImmutable
    {
        return $this->issueDate;
    }

    public function setIssueDate(\DateTimeImmutable $issueDate): self
    {
        $this->issueDate = $issueDate;

        return $this;
    }

    public function getMpn(): ?string
    {
        return $this->mpn;
    }

    public function setMpn(string $mpn): self
    {
        $this->mpn = $mpn;

        return $this;
    }

    public function getManufacturer(): ?Manufacturer
    {
        return $this->manufacturer;
    }

    public function setManufacturer(?Manufacturer $manufacturer): self
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }
}
