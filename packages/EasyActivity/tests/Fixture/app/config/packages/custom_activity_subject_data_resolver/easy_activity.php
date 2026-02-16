<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyActivity\Tests\Fixture\App\Entity\Author;

/**
 * @see \EonX\EasyActivity\Tests\Unit\Common\Factory\ActivityLogEntryFactoryTest::testCreateSucceedsWithCustomSubjectDataResolver
 */
return App::config([
    'easy_activity' => [
        'subjects' => [
            Author::class => [],
        ],
    ],
]);
