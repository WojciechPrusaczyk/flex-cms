<?php

namespace App\Entity;

use App\Repository\DashboardSettingsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DashboardSettingsRepository::class)]
class DashboardSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $englishName = null;

    #[ORM\Column(length: 255)]
    private ?string $iconFileName = null;

    #[ORM\Column]
    private ?bool $isActive = null;

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

    public function getEnglishName(): ?string
    {
        return $this->englishName;
    }

    public function setEnglishName(string $englishName): static
    {
        $this->englishName = $englishName;

        return $this;
    }

    public function getIconFileName(): ?string
    {
        return $this->iconFileName;
    }

    public function setIconFileName(string $iconFileName): static
    {
        $this->iconFileName = $iconFileName;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }
}
