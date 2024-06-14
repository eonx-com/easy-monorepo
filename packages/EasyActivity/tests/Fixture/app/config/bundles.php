<?php
declare(strict_types=1);

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use EonX\EasyActivity\Bundle\EasyActivityBundle;
use EonX\EasyDoctrine\Bridge\Symfony\EasyDoctrineSymfonyBundle;
use EonX\EasyEventDispatcher\Bridge\Symfony\EasyEventDispatcherSymfonyBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;

return [
    EasyActivityBundle::class => [
        'all' => true,
    ],
    EasyEventDispatcherSymfonyBundle::class => [
        'all' => true,
    ],
    EasyDoctrineSymfonyBundle::class => [
        'all' => true,
    ],
    DoctrineBundle::class => [
        'all' => true,
    ],
    FrameworkBundle::class => [
        'all' => true,
    ],
];
