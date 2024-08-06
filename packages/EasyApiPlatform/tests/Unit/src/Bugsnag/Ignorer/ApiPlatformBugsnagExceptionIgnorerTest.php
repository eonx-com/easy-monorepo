<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Unit\Bugsnag\Ignorer;

use ApiPlatform\Validator\Exception\ValidationException;
use EonX\EasyApiPlatform\Tests\Fixture\App\BugsnagExceptionIgnorer\Helper\IgnorerHelper;
use EonX\EasyApiPlatform\Tests\Unit\AbstractUnitTestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

final class ApiPlatformBugsnagExceptionIgnorerTest extends AbstractUnitTestCase
{
    public function testItDoesNotReportExceptionsToBugsnag(): void
    {
        $ignorerHelper = self::getService(IgnorerHelper::class);
        $constraintViolationList = new ConstraintViolationList([
            new ConstraintViolation('Some message', null, [], null, 'property', null),
        ]);
        $exception = new ValidationException($constraintViolationList);

        self::assertTrue($ignorerHelper->isIgnored($exception));
    }

    public function testItReportExceptionsToBugsnag(): void
    {
        self::bootKernel(['environment' => 'report_exception_to_bugsnag']);
        $ignorerHelper = self::getService(IgnorerHelper::class);
        $constraintViolationList = new ConstraintViolationList([
            new ConstraintViolation('Some message', null, [], null, 'property', null),
        ]);
        $exception = new ValidationException($constraintViolationList);

        self::assertFalse($ignorerHelper->isIgnored($exception));
    }
}
