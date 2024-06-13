<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyActivity\Tests\Bridge\Symfony\Fixtures\App\Entity\Author;
use Symfony\Config\EasyActivityConfig;

/**
 * @see \EonX\EasyActivity\Tests\ActivityLogEntryFactoryResolversTest::testCreateSucceedsWithCustomSubjectDataResolver
 */
return static function (EasyActivityConfig $easyActivityConfig): void {
    $easyActivityConfig->subjects(Author::class);
};
