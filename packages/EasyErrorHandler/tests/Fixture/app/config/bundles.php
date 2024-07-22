<?php
declare(strict_types=1);

use EonX\EasyErrorHandler\Bundle\EasyErrorHandlerBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;

return [
    EasyErrorHandlerBundle::class => [
        'all' => true,
    ],
    FrameworkBundle::class => [
        'all' => true,
    ],
];
