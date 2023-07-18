<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Fixtures;

use Carbon\Carbon;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Article
{
    #[ORM\ManyToOne(targetEntity: Author::class)]
    private Author $author;

    /**
     * @var \Doctrine\Common\Collections\Collection<string|int, \EonX\EasyActivity\Tests\Fixtures\Comment>
     */
    #[ORM\OneToMany(mappedBy: 'article', targetEntity: Comment::class, cascade: ['persist'])]
    private Collection $comments;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $content;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE)]
    private DateTimeInterface $createdAt;

    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private int $id;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $title;

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

    public function getAuthor(): Author
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

    public function getId(): int
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

    public function setId(int $id): self
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
