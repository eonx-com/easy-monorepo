<?php
declare(strict_types=1);

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use EonX\EasyEventDispatcher\Bridge\Symfony\EasyEventDispatcherSymfonyBundle;
use EonX\EasyHttpClient\Bridge\Symfony\EasyHttpClientSymfonyBundle;
use EonX\EasyLock\Bridge\Symfony\EasyLockSymfonyBundle;
use EonX\EasyWebhook\Bundle\EasyWebhookBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;

return [
    DoctrineBundle::class => [
        'all' => true,
    ],
    EasyEventDispatcherSymfonyBundle::class => [
        'all' => true,
    ],
    EasyWebhookBundle::class => [
        'all' => true,
    ],
    EasyLockSymfonyBundle::class => [
        'all' => true,
    ],
    EasyHttpClientSymfonyBundle::class => [
        'all' => true,
    ],
    FrameworkBundle::class => [
        'all' => true,
    ],
];
