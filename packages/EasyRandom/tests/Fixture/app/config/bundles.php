<?php
declare(strict_types=1);

use EonX\EasyRandom\Bundle\EasyRandomBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;

return [
    EasyRandomBundle::class => [
        'all' => true,
    ],
    FrameworkBundle::class => [
        'all' => true,
    ],
];
