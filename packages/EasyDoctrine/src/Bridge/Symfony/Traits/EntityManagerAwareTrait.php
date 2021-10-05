<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Bridge\Symfony\Traits;

use Doctrine\ORM\EntityManagerInterface;

trait EntityManagerAwareTrait
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @required
     */
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }
}
