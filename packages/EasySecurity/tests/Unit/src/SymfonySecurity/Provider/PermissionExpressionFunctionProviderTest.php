<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Unit\SymfonySecurity\Provider;

use EonX\EasySecurity\SymfonySecurity\Exception\PermissionConstantNotFoundException;
use EonX\EasySecurity\SymfonySecurity\Provider\PermissionExpressionFunctionProvider;
use EonX\EasySecurity\Tests\Unit\AbstractUnitTestCase;

final class PermissionExpressionFunctionProviderTest extends AbstractUnitTestCase
{
    public const string PERMISSION_VALID = 'permission';

    public function testPermissionExpressionFunctionFoundWithConstant(): void
    {
        $function = new PermissionExpressionFunctionProvider([self::class])->getFunctions()[0];

        $evaluator = $function->getEvaluator();

        $result = $evaluator([], 'PERMISSION_VALID');
        // Using cached permission
        $cachedResult = $evaluator([], 'PERMISSION_VALID');

        self::assertSame('permission', $result);
        self::assertSame('permission', $cachedResult);
    }

    public function testPermissionExpressionFunctionFoundWithEnum(): void
    {
        $function = new PermissionExpressionFunctionProvider([PermissionEnum::class])->getFunctions()[0];

        $evaluator = $function->getEvaluator();

        $result = $evaluator([], 'PermissionValid');
        // Using cached permission
        $cachedResult = $evaluator([], 'PermissionValid');

        self::assertSame(PermissionEnum::PermissionValid->value, $result);
        self::assertSame(PermissionEnum::PermissionValid->value, $cachedResult);
    }

    public function testPermissionExpressionFunctionNotFoundWithConstant(): void
    {
        $this->expectException(PermissionConstantNotFoundException::class);

        $function = new PermissionExpressionFunctionProvider([self::class])->getFunctions()[0];
        $function->getEvaluator()([], 'PERMISSION_INVALID');
    }

    public function testPermissionExpressionFunctionNotFoundWithEnum(): void
    {
        $this->expectException(PermissionConstantNotFoundException::class);

        $function = new PermissionExpressionFunctionProvider([PermissionEnum::class])->getFunctions()[0];
        $function->getEvaluator()([], 'PermissionInvalid');
    }
}

enum PermissionEnum: string
{
    case PermissionValid = 'permission';
}
