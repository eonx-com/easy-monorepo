<?php

declare(strict_types=1);

return [
    ApiPlatform\Symfony\Bundle\ApiPlatformBundle::class => [
        'all' => true,
    ],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => [
        'all' => true,
    ],
    EonX\EasyApiPlatform\Bridge\Symfony\EasyApiPlatformSymfonyBundle::class => [
        'all' => true,
    ],
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => [
        'all' => true,
    ],
];
