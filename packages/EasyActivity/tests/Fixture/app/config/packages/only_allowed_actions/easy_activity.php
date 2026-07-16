<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyActivity\Common\Enum\ActivityAction;
use EonX\EasyActivity\Tests\Fixture\App\Entity\Article;

/**
 * @see \EonX\EasyActivity\Tests\Unit\Common\Factory\ActivityLogEntryFactoryTest::testCreateSucceedsWithAllowedActions
 */
return App::config([
    'easy_activity' => [
        'subjects' => [
            Article::class => [
                'allowed_actions' => [
                    ActivityAction::Update,
                ],
            ],
        ],
    ],
]);
