<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyActivity\Common\Enum\ActivityAction;
use EonX\EasyActivity\Tests\Fixture\App\Entity\Article;
use Symfony\Config\EasyActivityConfig;

/**
 * @see \EonX\EasyActivity\Tests\Unit\Common\Factory\ActivityLogEntryFactoryTest::testCreateSucceedsWithAllowedActions
 */
return static function (EasyActivityConfig $easyActivityConfig): void {
    $easyActivityConfig->subjects(Article::class)
        ->allowedActions([
            ActivityAction::Update,
        ]);
};
