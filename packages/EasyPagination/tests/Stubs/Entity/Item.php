<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Stubs\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Item
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
     * @ORM\Column(type="string", length=191, nullable=true)
     *
     * @var string
     */
    public $title;
}
