<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyActivity\Tests\Fixture\App\Entity\Article;
use EonX\EasyActivity\Tests\Fixture\App\Entity\Comment;

return App::config([
    'easy_doctrine' => [
        'deferred_dispatcher_entities' => [
            Article::class,
            Comment::class,
        ],
    ],
]);
