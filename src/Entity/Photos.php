<?php

namespace App\Entity;

use App\Repository\PhotosRepository;
use Doctrine\DBAL\Types\Types;
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
    private ?string $fileName = null;

    #[ORM\Column(length: 255)]
    private ?string $safeFileName = null;

    #[ORM\Column(length: 15)]
    private ?string $fileType = null;

    #[ORM\ManyToOne(inversedBy: 'photos')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Admin $addedBY = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $addedDatetime = null;

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

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): static
    {
        $this->fileName = $fileName;

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

    public function getSafeFileName(): ?string
    {
        return $this->safeFileName;
    }

    public function setSafeFileName(string $safeFileName): static
    {
        $this->safeFileName = $safeFileName;

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

    public function getAddedDatetime(): ?\DateTimeInterface
    {
        return $this->addedDatetime;
    }

    public function setAddedDatetime(\DateTimeInterface $addedDatetime): static
    {
        $this->addedDatetime = $addedDatetime;

        return $this;
    }
}
