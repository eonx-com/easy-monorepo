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
class SimpleEntity
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Id
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $property;

    /**
     * Gets id.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Gets property.
     *
     * @return string
     */
    public function getProperty(): string
    {
        return $this->property;
    }

    /**
     * Sets property.
     *
     * @param string $property
     *
     * @return void
     */
    public function setProperty(string $property): void
    {
        $this->property = $property;
    }
}
