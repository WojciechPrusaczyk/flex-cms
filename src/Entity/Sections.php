<?php

namespace App\Entity;

use App\Repository\SectionsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SectionsRepository::class)]
class Sections
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    #[ORM\ManyToOne(inversedBy: 'sections')]
    private ?Admin $addedBy = null;

    #[ORM\ManyToOne(inversedBy: 'sections')]
    private ?admin $editedBy = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $startBeingActive = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $stopBeingActive = null;

    #[ORM\Column(nullable: true)]
    private ?array $value = null;

    #[ORM\Column(nullable: true)]
    private ?int $position = null;

    #[ORM\Column]
    private ?bool $isWide = null;

    #[ORM\Column]
    private ?bool $isTitleVisible = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getAddedBy(): ?Admin
    {
        return $this->addedBy;
    }

    public function setAddedBy(?Admin $addedBy): static
    {
        $this->addedBy = $addedBy;

        return $this;
    }

    public function getEditedBy(): ?admin
    {
        return $this->editedBy;
    }

    public function setEditedBy(?admin $editedBy): static
    {
        $this->editedBy = $editedBy;

        return $this;
    }

    public function getStartBeingActive(): ?\DateTimeInterface
    {
        return $this->startBeingActive;
    }

    public function setStartBeingActive(?\DateTimeInterface $startBeingActive): static
    {
        $this->startBeingActive = $startBeingActive;

        return $this;
    }

    public function getStopBeingActive(): ?\DateTimeInterface
    {
        return $this->stopBeingActive;
    }

    public function setStopBeingActive(?\DateTimeInterface $stopBeingActive): static
    {
        $this->stopBeingActive = $stopBeingActive;

        return $this;
    }

    public function getValue(): ?array
    {
        return $this->value;
    }

    public function setValue(?array $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function isWide(): ?bool
    {
        return $this->isWide;
    }

    public function setWide(bool $isWide): static
    {
        $this->isWide = $isWide;

        return $this;
    }

    public function isTitleVisible(): ?bool
    {
        return $this->isTitleVisible;
    }

    public function setIsTitleVisible(bool $isTitleVisible): static
    {
        $this->isTitleVisible = $isTitleVisible;

        return $this;
    }
}
