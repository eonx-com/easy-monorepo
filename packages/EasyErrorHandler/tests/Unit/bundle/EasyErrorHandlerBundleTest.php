<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Unit\Bundle;

use EonX\EasyErrorHandler\Bugsnag\Provider\BugsnagErrorReporterProvider;
use EonX\EasyErrorHandler\Tests\Unit\AbstractUnitTestCase;

final class EasyErrorHandlerBundleTest extends AbstractUnitTestCase
{
    public function testItSucceedsWithEasyBugsnag(): void
    {
        self::bootKernel(['environment' => 'with_easy_bugsnag']);

        self::getService(BugsnagErrorReporterProvider::class);

        self::assertTrue(true);
    }
}
