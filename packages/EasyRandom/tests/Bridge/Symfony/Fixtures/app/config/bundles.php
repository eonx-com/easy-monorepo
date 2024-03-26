<?php
declare(strict_types=1);

use EonX\EasyRandom\Bridge\Symfony\EasyRandomSymfonyBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;

return [
    EasyRandomSymfonyBundle::class => [
        'all' => true,
    ],
    FrameworkBundle::class => [
        'all' => true,
    ],
];
