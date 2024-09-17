<?php
declare(strict_types=1);

use EonX\EasyBugsnag\Bundle\EasyBugsnagBundle;
use EonX\EasyErrorHandler\Bundle\EasyErrorHandlerBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;

return [
    EasyBugsnagBundle::class => [
        'with_easy_bugsnag' => true,
    ],
    EasyErrorHandlerBundle::class => [
        'all' => true,
    ],
    FrameworkBundle::class => [
        'all' => true,
    ],
];
