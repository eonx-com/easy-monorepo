<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Bridge\Symfony\Security;

use EonX\EasySecurity\Bridge\Symfony\Exceptions\PermissionConstantNotFoundException;
use EonX\EasySecurity\Bridge\Symfony\Security\PermissionExpressionFunctionProvider;
use EonX\EasySecurity\Tests\AbstractTestCase;

final class PermissionExpressionFunctionProviderTest extends AbstractTestCase
{
    public const PERMISSION_VALID = 'permission';

    public function testPermissionExpressionFunctionFound(): void
    {
        $function = (new PermissionExpressionFunctionProvider([self::class]))->getFunctions()[0];

        self::assertEquals(self::PERMISSION_VALID, $function->getEvaluator()([], 'PERMISSION_VALID'));
        // Using cached permission
        self::assertEquals(self::PERMISSION_VALID, $function->getEvaluator()([], 'PERMISSION_VALID'));
    }

    public function testPermissionExpressionFunctionNotFound(): void
    {
        $this->expectException(PermissionConstantNotFoundException::class);

        $function = (new PermissionExpressionFunctionProvider([self::class]))->getFunctions()[0];
        $function->getEvaluator()([], 'PERMISSION_INVALID');
    }
}
