<?php

namespace App\Entity;

use App\Repository\TrickHistoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\UuidV6;

#[ORM\Entity(repositoryClass: TrickHistoryRepository::class)]
class TrickHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?UuidV6 $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'trickHistories')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Trick $trick = null;

    #[ORM\ManyToOne(cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\Column]
    private ?bool $isNameUpdated = false;

    #[ORM\Column]
    private ?bool $isDescriptionUpdated = false;

    #[ORM\Column]
    private ?bool $isGroupTrickUpdated = false;

    #[ORM\Column]
    private ?bool $isMediaFeatured = false;

    #[ORM\Column]
    private ?bool $isMediaImageAdded = false;

    #[ORM\Column]
    private ?bool $isMediaVideoAdded = false;

    #[ORM\Column]
    private ?bool $isMediaImageUpdated = false;

    #[ORM\Column]
    private ?bool $isMediaVideoUpdated = false;

    #[ORM\Column]
    private ?bool $isMediaImageDeleted = false;

    #[ORM\Column]
    private ?bool $isMediaVideoDeleted = false;

    public function getId(): ?UuidV6
    {
        return $this->id;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getTrick(): ?Trick
    {
        return $this->trick;
    }

    public function setTrick(?Trick $trick): self
    {
        $this->trick = $trick;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function isIsNameUpdated(): ?bool
    {
        return $this->isNameUpdated;
    }

    public function setIsNameUpdated(bool $isNameUpdated): static
    {
        $this->isNameUpdated = $isNameUpdated;

        return $this;
    }

    public function isIsDescriptionUpdated(): ?bool
    {
        return $this->isDescriptionUpdated;
    }

    public function setIsDescriptionUpdated(bool $isDescriptionUpdated): static
    {
        $this->isDescriptionUpdated = $isDescriptionUpdated;

        return $this;
    }

    public function isIsGroupTrickUpdated(): ?bool
    {
        return $this->isGroupTrickUpdated;
    }

    public function setIsGroupTrickUpdated(bool $isGroupTrickUpdated): static
    {
        $this->isGroupTrickUpdated = $isGroupTrickUpdated;

        return $this;
    }

    public function isIsMediaFeatured(): ?bool
    {
        return $this->isMediaFeatured;
    }

    public function setIsMediaFeatured(bool $isMediaFeatured): static
    {
        $this->isMediaFeatured = $isMediaFeatured;

        return $this;
    }

    public function isIsMediaImageAdded(): ?bool
    {
        return $this->isMediaImageAdded;
    }

    public function setIsMediaImageAdded(bool $isMediaImageAdded): static
    {
        $this->isMediaImageAdded = $isMediaImageAdded;

        return $this;
    }

    public function isMediaVideoAdded(): ?bool
    {
        return $this->isMediaVideoAdded;
    }

    public function setIsMediaVideoAdded(bool $isMediaVideoAdded): static
    {
        $this->isMediaVideoAdded = $isMediaVideoAdded;

        return $this;
    }

    public function isIsMediaImageUpdated(): ?bool
    {
        return $this->isMediaImageUpdated;
    }

    public function setIsMediaImageUpdated(bool $isMediaImageUpdated): static
    {
        $this->isMediaImageUpdated = $isMediaImageUpdated;

        return $this;
    }

    public function isIsMediaVideoUpdated(): ?bool
    {
        return $this->isMediaVideoUpdated;
    }

    public function setIsMediaVideoUpdated(bool $isMediaVideoUpdated): static
    {
        $this->isMediaVideoUpdated = $isMediaVideoUpdated;

        return $this;
    }

    public function isIsMediaImageDeleted(): ?bool
    {
        return $this->isMediaImageDeleted;
    }

    public function setIsMediaImageDeleted(bool $isMediaImageDeleted): static
    {
        $this->isMediaImageDeleted = $isMediaImageDeleted;

        return $this;
    }

    public function isIsMediaVideoDeleted(): ?bool
    {
        return $this->isMediaVideoDeleted;
    }

    public function setIsMediaVideoDeleted(bool $isMediaVideoDeleted): static
    {
        $this->isMediaVideoDeleted = $isMediaVideoDeleted;

        return $this;
    }
}
