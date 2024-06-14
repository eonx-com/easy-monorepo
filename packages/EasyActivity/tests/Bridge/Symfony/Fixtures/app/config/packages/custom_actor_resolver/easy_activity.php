<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyActivity\Tests\Bridge\Symfony\Fixtures\App\Entity\Article;
use Symfony\Config\EasyActivityConfig;

/**
 * @see \EonX\EasyActivity\Tests\Bridge\EasyDoctrine\EasyDoctrineEntityEventsSubscriberTest::testLoggerSucceedsWithCustomActorResolver
 */
return static function (EasyActivityConfig $easyActivityConfig): void {
    $easyActivityConfig->subjects(Article::class);
};
