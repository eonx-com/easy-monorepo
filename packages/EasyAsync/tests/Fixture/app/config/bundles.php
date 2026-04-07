<?php
declare(strict_types=1);

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use EonX\EasyAsync\Bundle\EasyAsyncBundle;
use EonX\EasyLogging\Bundle\EasyLoggingBundle;
use EonX\EasyServerless\Bundle\EasyServerlessBundle;
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
    EasyServerlessBundle::class => [
        'serverless' => true,
        'serverless_close_disabled' => true,
    ],
    FrameworkBundle::class => [
        'all' => true,
    ],
];
