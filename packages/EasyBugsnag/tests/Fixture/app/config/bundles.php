<?php
declare(strict_types=1);

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use EonX\EasyBugsnag\Bundle\EasyBugsnagBundle;
use EonX\EasyUtils\Bundle\EasyUtilsBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;

return [
    EasyUtilsBundle::class => [
        'all' => true,
    ],
    EasyBugsnagBundle::class => [
        'all' => true,
    ],
    DoctrineBundle::class => [
        'all' => true,
    ],
    FrameworkBundle::class => [
        'all' => true,
    ],
];
