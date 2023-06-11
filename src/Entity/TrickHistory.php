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

    #[ORM\Column(length: 255, unique: true)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?MediaTrick $pictureFeatured = null;

    #[ORM\ManyToMany(targetEntity: MediaTrick::class, inversedBy: 'trickHistories')]
    private Collection $mediasTrick;

    #[ORM\ManyToOne(inversedBy: 'trickHistories')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Trick $trick = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?GroupTrick $groupTrick = null;

    #[ORM\ManyToOne(inversedBy: 'trickHistories')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    public function __construct()
    {
        $this->mediasTrick = new ArrayCollection();
    }

    public function getId(): ?UuidV6
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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getPictureFeatured(): ?MediaTrick
    {
        return $this->pictureFeatured;
    }

    public function setPictureFeatured(?MediaTrick $pictureFeatured): self
    {
        $this->pictureFeatured = $pictureFeatured;

        return $this;
    }

    /**
     * @return Collection<int, MediaTrick>
     */
    public function getMediasTrick(): Collection
    {
        return $this->mediasTrick;
    }

    public function addMediasTrick(MediaTrick $mediasTrick): self
    {
        if (!$this->mediasTrick->contains($mediasTrick)) {
            $this->mediasTrick->add($mediasTrick);
        }

        return $this;
    }

    public function removeMediasTrick(MediaTrick $mediasTrick): self
    {
        $this->mediasTrick->removeElement($mediasTrick);

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

    public function getGroupTrick(): ?GroupTrick
    {
        return $this->groupTrick;
    }

    public function setGroupTrick(?GroupTrick $groupTrick): self
    {
        $this->groupTrick = $groupTrick;

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
}
