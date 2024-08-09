<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyDoctrine\Tests\Fixture\App\Entity\Product;
use EonX\EasyDoctrine\Tests\Fixture\App\Entity\Tag;
use Symfony\Config\EasyDoctrineConfig;

return static function (EasyDoctrineConfig $easyDoctrineConfig): void {
    $easyDoctrineConfig
        ->deferredDispatcherEntities([
            Product::class,
            Tag::class,
        ]);
};
