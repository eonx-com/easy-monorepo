<?php
declare(strict_types=1);

use ApiPlatform\Symfony\Bundle\ApiPlatformBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use EonX\EasyApiPlatform\Bundle\EasyApiPlatformBundle;
use EonX\EasyBugsnag\Bundle\EasyBugsnagBundle;
use EonX\EasyErrorHandler\Bundle\EasyErrorHandlerBundle;
use EonX\EasyLogging\Bundle\EasyLoggingBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;

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
    EasyErrorHandlerBundle::class => [
        'all' => true,
    ],
    EasyLoggingBundle::class => [
        'all' => true,
    ],
    EasyBugsnagBundle::class => [
        'all' => true,
    ],
    FrameworkBundle::class => [
        'all' => true,
    ],
    TwigBundle::class => [
        'all' => true,
    ],
    SecurityBundle::class => [
        'all' => true,
    ],
];
