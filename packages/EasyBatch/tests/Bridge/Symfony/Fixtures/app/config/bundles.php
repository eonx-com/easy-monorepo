<?php

declare(strict_types=1);

return [
    EonX\EasyBatch\Bridge\Symfony\EasyBatchSymfonyBundle::class => [
        'all' => true,
    ],
    EonX\EasyEncryption\Bridge\Symfony\EasyEncryptionSymfonyBundle::class => [
        'all' => true,
    ],
    EonX\EasyEventDispatcher\Bridge\Symfony\EasyEventDispatcherSymfonyBundle::class => [
        'all' => true,
    ],
    EonX\EasyRandom\Bridge\Symfony\EasyRandomSymfonyBundle::class => [
        'all' => true,
    ],
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => [
        'all' => true,
    ],
    EonX\EasyLock\Bridge\Symfony\EasyLockSymfonyBundle::class => [
        'all' => true,
    ],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => [
        'all' => true,
    ],
];
