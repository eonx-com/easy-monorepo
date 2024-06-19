<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyActivity\Tests\Fixture\App\Entity\Author;
use Symfony\Config\EasyActivityConfig;

/**
 * @see \EonX\EasyActivity\Tests\Unit\Common\Factory\ActivityLogEntryFactoryTest::testCreateSucceedsWithCustomSubjectDataResolver
 */
return static function (EasyActivityConfig $easyActivityConfig): void {
    $easyActivityConfig->subjects(Author::class);
};
