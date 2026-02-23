<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyActivity\Tests\Fixture\App\Entity\Article;

/**
 * @see \EonX\EasyActivity\Tests\Unit\Common\Factory\ActivityLogEntryFactoryTest::testCreateSucceeds
 * @see \EonX\EasyActivity\Tests\Unit\Common\Factory\ActivityLogEntryFactoryTest::testCreateSucceedsWithCollections
 * @see \EonX\EasyActivity\Tests\Unit\Common\Factory\ActivityLogEntryFactoryTest::testCreateSucceedsWithRelatedObjects
 * @see \EonX\EasyActivity\Tests\Unit\EasyDoctrine\Subscriber\EasyDoctrineEntityEventsSubscriberTest::testLoggerSucceedsForDeletedSubjects
 */
return App::config([
    'easy_activity' => [
        'subjects' => [
            Article::class => [],
        ],
    ],
]);
