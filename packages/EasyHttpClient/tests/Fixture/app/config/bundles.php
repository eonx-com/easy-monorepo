<?php
declare(strict_types=1);

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use EonX\EasyEventDispatcher\Bundle\EasyEventDispatcherBundle;
use EonX\EasyHttpClient\Bundle\EasyHttpClientBundle;
use EonX\EasyLock\Bridge\Symfony\EasyLockSymfonyBundle;
use EonX\EasyWebhook\Bridge\Symfony\EasyWebhookSymfonyBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;

return [
    DoctrineBundle::class => [
        'all' => true,
    ],
    EasyEventDispatcherBundle::class => [
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
