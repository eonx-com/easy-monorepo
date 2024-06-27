<?php
declare(strict_types=1);

use ApiPlatform\Symfony\Bundle\ApiPlatformBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use EonX\EasyErrorHandler\Bundle\EasyErrorHandlerBundle;
use EonX\EasyLogging\Bundle\EasyLoggingBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;

return [
    ApiPlatformBundle::class => [
        'all' => true,
    ],
    DoctrineBundle::class => [
        'all' => true,
    ],
    EasyErrorHandlerBundle::class => [
        'all' => true,
    ],
    FrameworkBundle::class => [
        'all' => true,
    ],
    EasyLoggingBundle::class => [
        'all' => true,
    ],
];
