<?php
declare(strict_types=1);

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use EonX\EasyActivity\Bundle\EasyActivityBundle;
use EonX\EasyDoctrine\Bundle\EasyDoctrineBundle;
use EonX\EasyEncryption\Bundle\EasyEncryptionBundle;
use EonX\EasyEventDispatcher\Bundle\EasyEventDispatcherBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;

return [
    EasyActivityBundle::class => [
        'all' => true,
    ],
    EasyEventDispatcherBundle::class => [
        'all' => true,
    ],
    EasyDoctrineBundle::class => [
        'all' => true,
    ],
    EasyEncryptionBundle::class => [
        'encryptable_fields_integration' => true,
    ],
    DoctrineBundle::class => [
        'all' => true,
    ],
    FrameworkBundle::class => [
        'all' => true,
    ],
];
