<?php
declare(strict_types=1);

use ApiPlatform\Symfony\Bundle\ApiPlatformBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use EonX\EasyApiPlatform\Bundle\EasyApiPlatformBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;

return [
    ApiPlatformBundle::class => [
        'all' => true,
    ],
    DoctrineBundle::class => [
        'all' => true,
    ],
    EasyApiPlatformBundle::class => [
        'all' => true,
    ],
    FrameworkBundle::class => [
        'all' => true,
    ],
];
