<?php

namespace App\Entity;

use App\Repository\AdminRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: AdminRepository::class)]
class Admin implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $username = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $accountCreated = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastLoginDate = null;

    #[ORM\Column]
    private ?bool $active = null;

    #[ORM\OneToMany(mappedBy: 'addedBY', targetEntity: Photos::class)]
    private Collection $photos;

    #[ORM\OneToMany(mappedBy: 'addedBy', targetEntity: StyleSheets::class)]
    private Collection $styleSheets;

    #[ORM\OneToMany(mappedBy: 'addedBy', targetEntity: Scripts::class)]
    private Collection $scripts;

    public function __construct()
    {
        $this->photos = new ArrayCollection();
        $this->styleSheets = new ArrayCollection();
        $this->scripts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getAccountCreated(): ?\DateTimeInterface
    {
        return $this->accountCreated;
    }

    public function setAccountCreated(\DateTimeInterface $accountCreated): static
    {
        $this->accountCreated = $accountCreated;

        return $this;
    }

    public function getLastLoginDate(): ?\DateTimeInterface
    {
        return $this->lastLoginDate;
    }

    public function setLastLoginDate(?\DateTimeInterface $lastLoginDate): static
    {
        $this->lastLoginDate = $lastLoginDate;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return Collection<int, Photos>
     */
    public function getPhotos(): Collection
    {
        return $this->photos;
    }

    public function addPhoto(Photos $photo): static
    {
        if (!$this->photos->contains($photo)) {
            $this->photos->add($photo);
            $photo->setAddedBY($this);
        }

        return $this;
    }

    public function removePhoto(Photos $photo): static
    {
        if ($this->photos->removeElement($photo)) {
            // set the owning side to null (unless already changed)
            if ($photo->getAddedBY() === $this) {
                $photo->setAddedBY(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, StyleSheets>
     */
    public function getStyleSheets(): Collection
    {
        return $this->styleSheets;
    }

    public function addStyleSheet(StyleSheets $styleSheet): static
    {
        if (!$this->styleSheets->contains($styleSheet)) {
            $this->styleSheets->add($styleSheet);
            $styleSheet->setAddedBy($this);
        }

        return $this;
    }

    public function removeStyleSheet(StyleSheets $styleSheet): static
    {
        if ($this->styleSheets->removeElement($styleSheet)) {
            // set the owning side to null (unless already changed)
            if ($styleSheet->getAddedBy() === $this) {
                $styleSheet->setAddedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Scripts>
     */
    public function getScript(): Collection
    {
        return $this->scripts;
    }

    public function addScript(Scripts $script): static
    {
        if (!$this->scripts->contains($script)) {
            $this->scripts->add($script);
            $script->setAddedBy($this);
        }

        return $this;
    }

    public function removeScript(Scripts $script): static
    {
        if ($this->scripts->removeElement($script)) {
            // set the owning side to null (unless already changed)
            if ($script->getAddedBy() === $this) {
                $script->setAddedBy(null);
            }
        }

        return $this;
    }
}
