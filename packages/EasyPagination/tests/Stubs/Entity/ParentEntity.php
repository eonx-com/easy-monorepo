<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Stubs\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="parents")
 */
class ParentEntity
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public int $id;

    /**
     * @ORM\ManyToOne(targetEntity="\EonX\EasyPagination\Tests\Stubs\Entity\Item")
     */
    public Item $item;

    /**
     * @ORM\Column(type="string", length=191, nullable=true)
     */
    public ?string $title = null;
}
