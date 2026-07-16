<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyDoctrine\Tests\Fixture\App\Entity\Category;
use EonX\EasyDoctrine\Tests\Fixture\App\Entity\Product;

return App::config([
    'easy_doctrine' => [
        'deferred_dispatcher_entities' => [
            Category::class,
            Product::class,
        ],
    ],
]);
