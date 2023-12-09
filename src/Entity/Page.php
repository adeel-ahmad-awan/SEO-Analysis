<?php

namespace App\Entity;

use App\Repository\PageRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageRepository::class)]
class Page
{
    /**
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    private ?string $url = null;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /**
     * @var DateTimeImmutable|null
     * @ORM\Column(type="datetime", options={"default"="CURRENT_TIMESTAMP"})
     * /
     */
    private ?DateTimeImmutable $createdAt = null;

    /**
     * @var DateTimeImmutable|null
     * @ORM\Column(type="datetime", options={"default"="CURRENT_TIMESTAMP"})
     */
    private ?DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true)]
    private ?array $issues = null;


    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageFile = null;

    #[ORM\OneToMany(mappedBy: 'page', targetEntity: MetaTag::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $metaTag;

    public function __construct()
    {
        $this->metaTag = new ArrayCollection();
    }


    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     * @return $this
     */
    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return $this
     */
    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @param DateTimeImmutable $createdAt
     * @return $this
     */
    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @ORM\PreUpdate
     * @return $this
     */
    public function setUpdatedAt(): static
    {
        $this->updatedAt = new DateTimeImmutable();
        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function setTimestampsOnCreate(): void
    {
        // Check if the createdAt property is not already set
        if ($this->createdAt === null) {
            $this->createdAt = new DateTimeImmutable();
        }
        // Always update the updatedAt property on create
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getIssues(): ?array
    {
        return $this->issues;
    }

    public function setIssues(?array $issues): static
    {
        $this->issues = $issues;
        return $this;
    }

    public function getImageFile(): ?string
    {
        return $this->imageFile;
    }

    public function setImageFile(?string $imageFile): static
    {
        $this->imageFile = $imageFile;
        return $this;
    }

    /**
     * @return Collection<int, MetaTag>
     */
    public function getMetaTag(): Collection
    {
        return $this->metaTag;
    }

    public function addMetaTag(MetaTag $metaTag): static
    {
        if (!$this->metaTag->contains($metaTag)) {
            $this->metaTag->add($metaTag);
            $metaTag->setPage($this);
        }

        return $this;
    }

    public function removeMetaTag(MetaTag $metaTag): static
    {
        if ($this->metaTag->removeElement($metaTag)) {
            // set the owning side to null (unless already changed)
            if ($metaTag->getPage() === $this) {
                $metaTag->setPage(null);
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function metaTagsToArray(): array
    {
        $metaTagsArray = [];
        foreach ($this->metaTag as $metaTag) {
            $metaTagsArray[] = $metaTag->toArray();
        }
        return $metaTagsArray;
    }
}
