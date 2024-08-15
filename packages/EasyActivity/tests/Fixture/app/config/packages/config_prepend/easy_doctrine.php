<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyActivity\Tests\Fixture\App\Entity\Article;
use EonX\EasyActivity\Tests\Fixture\App\Entity\Comment;
use Symfony\Config\EasyDoctrineConfig;

return static function (EasyDoctrineConfig $easyDoctrineConfig): void {
    $easyDoctrineConfig
        ->deferredDispatcherEntities([
            Article::class,
            Comment::class,
        ]);
};
