<?php
declare(strict_types=1);

use ApiPlatform\Symfony\Bundle\ApiPlatformBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use EonX\EasyErrorHandler\Bridge\Symfony\EasyErrorHandlerSymfonyBundle;
use EonX\EasyLogging\Bridge\Symfony\EasyLoggingSymfonyBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;

return [
    ApiPlatformBundle::class => [
        'all' => true,
    ],
    DoctrineBundle::class => [
        'all' => true,
    ],
    EasyErrorHandlerSymfonyBundle::class => [
        'all' => true,
    ],
    FrameworkBundle::class => [
        'all' => true,
    ],
    EasyLoggingSymfonyBundle::class => [
        'all' => true,
    ],
];
