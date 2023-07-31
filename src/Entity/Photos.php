<?php

namespace App\Entity;

use App\Repository\PhotosRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PhotosRepository::class)]
class Photos
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $filename = null;

    #[ORM\Column(length: 255)]
    private ?string $safefilename = null;

    #[ORM\Column(length: 15)]
    private ?string $fileType = null;

    #[ORM\ManyToOne(inversedBy: 'photos')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Admin $addedBY = null;

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

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    public function getFileType(): ?string
    {
        return $this->fileType;
    }

    public function setFileType(string $fileType): static
    {
        $this->fileType = $fileType;

        return $this;
    }

    public function getSafeFilename(): ?string
    {
        return $this->safefilename;
    }

    public function setSafeFilename(string $safefilename): static
    {
        $this->safefilename = $safefilename;

        return $this;
    }

    public function getAddedBY(): ?Admin
    {
        return $this->addedBY;
    }

    public function setAddedBY(?Admin $addedBY): static
    {
        $this->addedBY = $addedBY;

        return $this;
    }
}
