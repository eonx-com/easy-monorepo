<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Fixtures;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Comment
{
    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: 'comments')]
    private Article $article;

    #[ORM\Column(type: Types::GUID)]
    #[ORM\GeneratedValue(strategy: "UUID")]
    #[ORM\Id]
    private string $id;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $message;

    public function getArticle(): Article
    {
        return $this->article;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setArticle(Article $article): self
    {
        $this->article = $article;

        return $this;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }
}
