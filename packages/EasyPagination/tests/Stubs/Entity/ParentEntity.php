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
     *
     * @var int
     */
    public $id;

    /**
     * @ORM\ManyToOne(targetEntity="\EonX\EasyPagination\Tests\Stubs\Entity\Item")
     *
     * @var \EonX\EasyPagination\Tests\Stubs\Entity\Item
     */
    public $item;

    /**
     * @ORM\Column(type="string", length=191, nullable=true)
     *
     * @var string
     */
    public $title;
}
