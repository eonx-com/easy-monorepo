<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Bridge\Symfony\Security;

use EonX\EasySecurity\Bridge\Symfony\Exceptions\RoleConstantNotFoundException;
use EonX\EasySecurity\Bridge\Symfony\Security\RoleExpressionFunctionProvider;
use EonX\EasySecurity\Tests\AbstractTestCase;

final class RoleExpressionFunctionProviderTest extends AbstractTestCase
{
    public const ROLE_VALID = 'role';

    public function testRoleExpressionFunctionFoundWithConstant(): void
    {
        $function = (new RoleExpressionFunctionProvider([self::class]))->getFunctions()[0];

        self::assertSame(self::ROLE_VALID, $function->getEvaluator()([], 'ROLE_VALID'));
        // Using cached role
        self::assertSame(self::ROLE_VALID, $function->getEvaluator()([], 'ROLE_VALID'));
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
