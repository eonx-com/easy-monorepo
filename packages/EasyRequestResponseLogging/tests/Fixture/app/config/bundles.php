<?php
declare(strict_types=1);

use EonX\EasyLogging\Bundle\EasyLoggingBundle;
use EonX\EasyRandom\Bundle\EasyRandomBundle;
use EonX\EasyRequestId\Bundle\EasyRequestIdBundle;
use EonX\EasyRequestResponseLogging\Bundle\EasyRequestResponseLoggingBundle;
use EonX\EasyUtils\Bundle\EasyUtilsBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;

return [
    FrameworkBundle::class => [
        'all' => true,
    ],
    MonologBundle::class => [
        'all' => true,
    ],
    EasyUtilsBundle::class => [
        'all' => true,
    ],
    EasyRandomBundle::class => [
        'all' => true,
    ],
    EasyRequestIdBundle::class => [
        'all' => true,
        'test_easy_request_id_disabled' => false,
    ],
    EasyRequestResponseLoggingBundle::class => [
        'all' => true,
    ],
    EasyLoggingBundle::class => [
        'test_easy_logging' => true,
    ],
];
