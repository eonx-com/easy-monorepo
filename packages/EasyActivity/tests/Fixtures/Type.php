<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Fixtures;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Embeddable
 */
class Type
{
    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     *
     * @var string
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     *
     * @var string
     */
    private $title;

    public function __construct(string $description, string $title)
    {
        $this->title = $title;
        $this->description = $description;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }
}
