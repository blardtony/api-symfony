<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Common\Filter\SearchFilterInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Repository\ProductRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[
    ApiResource(
        collectionOperations: [
            'get',
            'post' => ['security' => 'is_granted("ROLE_ADMIN")']
        ],
        itemOperations: [
            'get',
            'put' => [
                'security' => 'is_granted("ROLE_USER") and object.getOwner() === user',
                'security_message' => 'A product can only be updated by the owner'
            ]
        ],
        attributes: ["pagination_items_per_page" => 5],
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
    #[Groups(['product.read', 'product.write'])]
    private ?\DateTimeImmutable $issueDate = null;

    /** The MPN (manufacturer part number) of the product */
    #[ORM\Column(length: 255)]
    #[Assert\NotNull]
    #[Groups(['product.read', 'product.write'])]
    private ?string $mpn = null;

    /** The manufacturer of the product */
    #[ORM\ManyToOne(inversedBy: 'products')]
    #[Groups(['product.read', 'product.write'])]
    #[Assert\NotNull]
    private ?Manufacturer $manufacturer = null;

    #[ORM\ManyToOne]
    #[Groups(['product.read', 'product.write'])]
    private ?User $owner = null;


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

    public function setIssueDate(\DatetimeInterface $issueDate): self
    {
        if($issueDate instanceof DatetimeImmutable){
            $this->issueDate = $issueDate;
            return $this;
        }
        $this->issueDate = DateTimeImmutable::createFromInterface($issueDate);

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

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }
}
