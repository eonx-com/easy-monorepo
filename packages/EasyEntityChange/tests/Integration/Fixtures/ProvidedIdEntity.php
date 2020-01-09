<?php
declare(strict_types=1);

namespace EonX\EasyEntityChange\Tests\Integration\Fixtures;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * @SuppressWarnings(PHPMD)
 *
 * @coversNothing
 */
class ProvidedIdEntity
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

    /**
     * Constructor
     *
     * @param string $id
     */
    public function __construct(string $id)
    {
        $this->id = $id;
    }

    /**
     * Gets id.
     *
     * @return string
     */
    public function getId(): string
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
