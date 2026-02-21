<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Unit\SymfonySecurity\Provider;

use EonX\EasySecurity\SymfonySecurity\Exception\RoleConstantNotFoundException;
use EonX\EasySecurity\SymfonySecurity\Provider\RoleExpressionFunctionProvider;
use EonX\EasySecurity\Tests\Unit\AbstractUnitTestCase;

final class RoleExpressionFunctionProviderTest extends AbstractUnitTestCase
{
    public const string ROLE_VALID = 'role';

    public function testRoleExpressionFunctionFoundWithConstant(): void
    {
        $function = (new RoleExpressionFunctionProvider([self::class]))->getFunctions()[0];

        self::assertSame('role', $function->getEvaluator()([], 'ROLE_VALID'));
        // Using cached role
        self::assertSame('role', $function->getEvaluator()([], 'ROLE_VALID'));
    }

    public function testRoleExpressionFunctionFoundWithEnum(): void
    {
        $function = (new RoleExpressionFunctionProvider([RoleEnum::class]))->getFunctions()[0];

        self::assertSame(RoleEnum::RoleValid->value, $function->getEvaluator()([], 'RoleValid'));
        // Using cached role
        self::assertSame(RoleEnum::RoleValid->value, $function->getEvaluator()([], 'RoleValid'));
    }

    public function testRoleExpressionFunctionNotFoundWithConstant(): void
    {
        $this->expectException(RoleConstantNotFoundException::class);

        $function = (new RoleExpressionFunctionProvider([self::class]))->getFunctions()[0];
        $function->getEvaluator()([], 'ROLE_INVALID');
    }

    public function testRoleExpressionFunctionNotFoundWithEnum(): void
    {
        $this->expectException(RoleConstantNotFoundException::class);

        $function = (new RoleExpressionFunctionProvider([RoleEnum::class]))->getFunctions()[0];
        $function->getEvaluator()([], 'RoleInvalid');
    }
}

enum RoleEnum: string
{
    case RoleValid = 'role';
}
