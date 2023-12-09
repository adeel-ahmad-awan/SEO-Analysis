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
     * @return array|null
     */
    public function getIssues(): ?array
    {
        return $this->issues;
    }

    /**
     * @param array|null $issues
     * @return $this
     */
    public function setIssues(?array $issues): static
    {
        $this->issues = $issues;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getImageFile(): ?string
    {
        return $this->imageFile;
    }

    /**
     * @param string|null $imageFile
     * @return $this
     */
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

    /**
     * @param MetaTag $metaTag
     * @return $this
     */
    public function addMetaTag(MetaTag $metaTag): static
    {
        if (!$this->metaTag->contains($metaTag)) {
            $this->metaTag->add($metaTag);
            $metaTag->setPage($this);
        }

        return $this;
    }

    /**
     * @param MetaTag $metaTag
     * @return $this
     */
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
