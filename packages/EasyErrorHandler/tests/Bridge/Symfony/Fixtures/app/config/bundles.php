<?php
declare(strict_types=1);

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use EonX\EasyErrorHandler\Bridge\Symfony\EasyErrorHandlerSymfonyBundle;
use EonX\EasyLogging\Bridge\Symfony\EasyLoggingSymfonyBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;

return [
    DoctrineBundle::class => [
        'all' => true,
    ],
    EasyErrorHandlerSymfonyBundle::class => [
        'all' => true,
    ],
    EasyLoggingSymfonyBundle::class => [
        'all' => true,
    ],
    FrameworkBundle::class => [
        'all' => true,
    ],
];
