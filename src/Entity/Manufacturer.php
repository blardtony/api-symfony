<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ManufacturerRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/** A manufacturer */
#[ORM\Entity(repositoryClass: ManufacturerRepository::class)]
#[ApiResource]
class Manufacturer
{
    /** The id of the manufacturer  */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** The name of the manufacturer  */
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /** The description of the manufacturer  */
    #[ORM\Column(length: 255)]
    private ?string $description = null;

    /** The country code of the manufacturer  */
    #[ORM\Column(length: 255)]
    private ?string $countryCode = null;

    /** The date that the manufacturer was listed */
    #[ORM\Column]
    private ?DateTimeImmutable $listedDate = null;

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

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(string $countryCode): self
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    public function getListedDate(): ?DateTimeImmutable
    {
        return $this->listedDate;
    }

    public function setListedDate(DateTimeImmutable $listedDate): self
    {
        $this->listedDate = $listedDate;

        return $this;
    }
}
