<?php
declare(strict_types=1);

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use EonX\EasyAsync\Bundle\EasyAsyncBundle;
use EonX\EasyLogging\Bundle\EasyLoggingBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;

return [
    DoctrineBundle::class => [
        'all' => true,
    ],
    EasyAsyncBundle::class => [
        'all' => true,
    ],
    EasyLoggingBundle::class => [
        'all' => true,
    ],
    FrameworkBundle::class => [
        'all' => true,
    ],
];
