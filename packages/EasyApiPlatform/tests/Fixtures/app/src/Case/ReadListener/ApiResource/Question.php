<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Fixtures\App\Case\ReadListener\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/questions/{id}/mark-as-answered',
            status: 200,
            input: false,
        ),
    ]
)]
#[ORM\Entity]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[Orm\Column(type: Types::STRING, nullable: true)]
    private string $question;

    public function getId(): int
    {
        return $this->id;
    }

    public function getQuestion(): string
    {
        return $this->question;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function setQuestion(string $question): self
    {
        $this->question = $question;

        return $this;
    }
}
