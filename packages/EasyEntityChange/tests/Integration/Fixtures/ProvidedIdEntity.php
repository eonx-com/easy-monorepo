<?php

declare(strict_types=1);

namespace EonX\EasyEntityChange\Tests\Integration\Fixtures;

use Doctrine\ORM\Mapping as ORM;

/**
 * @coversNothing
 *
 * @ORM\Entity
 *
 * @SuppressWarnings(PHPMD)
 */
final class ProvidedIdEntity
{
    /**
     * @ORM\Column(type="guid")
     * @ORM\Id
     *
     * @var string
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $property;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    public function setProperty(string $property): void
    {
        $this->property = $property;
    }
}
