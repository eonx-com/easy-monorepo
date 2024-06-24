<?php
declare(strict_types=1);

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use EonX\EasyEventDispatcher\Bridge\Symfony\EasyEventDispatcherSymfonyBundle;
use EonX\EasyHttpClient\Bundle\EasyHttpClientBundle;
use EonX\EasyLock\Bridge\Symfony\EasyLockSymfonyBundle;
use EonX\EasyWebhook\Bridge\Symfony\EasyWebhookSymfonyBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;

return [
    DoctrineBundle::class => [
        'all' => true,
    ],
    EasyEventDispatcherSymfonyBundle::class => [
        'all' => true,
    ],
    EasyWebhookSymfonyBundle::class => [
        'all' => true,
    ],
    EasyLockSymfonyBundle::class => [
        'all' => true,
    ],
    EasyHttpClientBundle::class => [
        'all' => true,
    ],
    FrameworkBundle::class => [
        'all' => true,
    ],
];
