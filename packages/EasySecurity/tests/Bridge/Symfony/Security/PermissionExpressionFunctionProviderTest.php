<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Bridge\Symfony\Security;

use EonX\EasySecurity\Bridge\Symfony\Exceptions\PermissionConstantNotFoundException;
use EonX\EasySecurity\Bridge\Symfony\Security\PermissionExpressionFunctionProvider;
use EonX\EasySecurity\Tests\AbstractTestCase;

final class PermissionExpressionFunctionProviderTest extends AbstractTestCase
{
    public const PERMISSION_VALID = 'permission';

    public function testPermissionExpressionFunctionFoundWithConstant(): void
    {
        $function = (new PermissionExpressionFunctionProvider([self::class]))->getFunctions()[0];

        self::assertSame(self::PERMISSION_VALID, $function->getEvaluator()([], 'PERMISSION_VALID'));
        // Using cached permission
        self::assertSame(self::PERMISSION_VALID, $function->getEvaluator()([], 'PERMISSION_VALID'));
    }

    public function testPermissionExpressionFunctionFoundWithEnum(): void
    {
        $function = (new PermissionExpressionFunctionProvider([PermissionEnum::class]))->getFunctions()[0];

        $enum = PermissionEnum::PermissionValid;

        self::assertSame(PermissionEnum::PermissionValid->value, $function->getEvaluator()([], 'PermissionValid'));
        // Using cached permission
        self::assertSame(PermissionEnum::PermissionValid->value, $function->getEvaluator()([], 'PermissionValid'));
    }

    public function testPermissionExpressionFunctionNotFoundWithConstant(): void
    {
        $this->expectException(PermissionConstantNotFoundException::class);

        $function = (new PermissionExpressionFunctionProvider([self::class]))->getFunctions()[0];
        $function->getEvaluator()([], 'PERMISSION_INVALID');
    }

    public function testPermissionExpressionFunctionNotFoundWithEnum(): void
    {
        $this->expectException(PermissionConstantNotFoundException::class);

        $function = (new PermissionExpressionFunctionProvider([PermissionEnum::class]))->getFunctions()[0];
        $function->getEvaluator()([], 'PermissionInvalid');
    }
}

enum PermissionEnum: string
{
    case PermissionValid = 'permission';
}
