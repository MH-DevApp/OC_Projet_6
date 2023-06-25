<?php

namespace App\Entity;

use App\Repository\TrickRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\UuidV6;

#[ORM\Entity(repositoryClass: TrickRepository::class)]
#[UniqueEntity("name", message: "Le nom de cette figure est déjà utilisé.")]
class Trick
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

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;


    #[ORM\Column]
    private ?bool $isPublished = false;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastUpdatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'tricks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\ManyToOne(inversedBy: 'tricks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?GroupTrick $groupTrick = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?MediaTrick $pictureFeatured = null;

    #[ORM\OneToMany(mappedBy: 'trick', targetEntity: TrickHistory::class, cascade: ['persist', 'remove'])]
    private Collection $trickHistories;

    #[ORM\OneToMany(mappedBy: 'trick', targetEntity: MediaTrick::class, cascade: ['persist', 'remove'])]
    private Collection $mediaTricks;

    #[ORM\OneToMany(mappedBy: 'trick', targetEntity: Comment::class, cascade: ['persist', 'remove'])]
    private Collection $comments;

    public function __construct()
    {
        $this->trickHistories = new ArrayCollection();
        $this->mediaTricks = new ArrayCollection();
        $this->comments = new ArrayCollection();
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function isPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(string $isPublished): self
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getLastUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->lastUpdatedAt;
    }

    public function setLastUpdatedAt(?\DateTimeImmutable $lastUpdatedAt): self
    {
        $this->lastUpdatedAt = $lastUpdatedAt;

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

    public function getGroupTrick(): ?GroupTrick
    {
        return $this->groupTrick;
    }

    public function setGroupTrick(?GroupTrick $groupTrick): self
    {
        $this->groupTrick = $groupTrick;

        return $this;
    }

    public function getPictureFeatured(): ?MediaTrick
    {
        return $this->pictureFeatured;
    }

    public function setPictureFeatured(?MediaTrick $pictureFeatured): self
    {
        $this->pictureFeatured = $pictureFeatured;
        if ($pictureFeatured && $this->pictureFeatured->getTrick() !== $this) {
            $this->pictureFeatured->setTrick($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, TrickHistory>
     */
    public function getTrickHistories(): Collection
    {
        return $this->trickHistories;
    }

    public function addTrickHistory(TrickHistory $trickHistory): self
    {
        if (!$this->trickHistories->contains($trickHistory)) {
            $this->trickHistories->add($trickHistory);
            $trickHistory->setTrick($this);
        }

        return $this;
    }

    public function removeTrickHistory(TrickHistory $trickHistory): self
    {
        if ($this->trickHistories->removeElement($trickHistory)) {
            // set the owning side to null (unless already changed)
            if ($trickHistory->getTrick() === $this) {
                $trickHistory->setTrick(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MediaTrick>
     */
    public function getMediaTricks(): Collection
    {
        return new ArrayCollection(array_filter(
            $this->mediaTricks->toArray(),
            function(MediaTrick $mediaTrick) {
                return $this->getPictureFeatured() !== $mediaTrick;
            }
        ));
    }

    public function addMediaTrick(MediaTrick $mediaTrick): self
    {
        if (!$this->mediaTricks->contains($mediaTrick)) {
            $this->mediaTricks->add($mediaTrick);
            $mediaTrick->setTrick($this);
        }

        return $this;
    }

    public function removeMediaTrick(MediaTrick $mediaTrick): self
    {
        if ($this->mediaTricks->removeElement($mediaTrick)) {
            // set the owning side to null (unless already changed)
            if ($mediaTrick->getTrick() === $this) {
                $mediaTrick->setTrick(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setTrick($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getTrick() === $this) {
                $comment->setTrick(null);
            }
        }

        return $this;
    }
}
