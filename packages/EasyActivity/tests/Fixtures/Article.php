<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Fixtures;

use Carbon\Carbon;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Article
{
    /**
     * @ORM\ManyToOne(targetEntity=\EonX\EasyActivity\Tests\Fixtures\Author::class)
     *
     * @var \EonX\EasyActivity\Tests\Fixtures\Author
     */
    private $author;

    /**
     * @ORM\OneToMany(targetEntity=\EonX\EasyActivity\Tests\Fixtures\Comment::class, mappedBy="article", cascade={"persist"})
     *
     * @var \Doctrine\Common\Collections\Collection<string|int, \EonX\EasyActivity\Tests\Fixtures\Comment>
     */
    private $comments;

    /**
     * @ORM\Column(type="text", length=256)
     *
     * @var string
     */
    private $content;

    /**
     * @ORM\Column(type="datetimetz")
     *
     * @var \DateTimeInterface
     */
    private $createdAt;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     *
     * @var string
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=256)
     *
     * @var string
     */
    private $title;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->createdAt = Carbon::now();
    }

    public function addComment(Comment $comment): self
    {
        if ($this->comments->contains($comment) === false) {
            $this->comments[] = $comment;
            $comment->setArticle($this);
        }

        return $this;
    }

    public function getAuthor(): ?Author
    {
        return $this->author;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection<string|int, \EonX\EasyActivity\Tests\Fixtures\Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setAuthor(Author $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }
}
