<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyActivity\Tests\Fixture\App\Entity\Article;
use Symfony\Config\EasyActivityConfig;

/**
 * @see \Application\src\Common\Factory\ActivityLogEntryFactoryTest::testCreateSucceeds
 * @see \Application\src\Common\Factory\ActivityLogEntryFactoryTest::testCreateSucceedsWithCollections
 * @see \Application\src\Common\Factory\ActivityLogEntryFactoryTest::testCreateSucceedsWithRelatedObjects
 * @see \Application\src\EasyDoctrine\Subscriber\EasyDoctrineEntityEventsSubscriberTest::testLoggerSucceedsForDeletedSubjects
 */
return static function (EasyActivityConfig $easyActivityConfig): void {
    $easyActivityConfig->subjects(Article::class);
};
