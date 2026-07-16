<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyDoctrine\Tests\Fixture\App\Entity\Category;

return App::config([
    'easy_doctrine' => [
        'deferred_dispatcher_entities' => [
            Category::class,
        ],
    ],
]);
