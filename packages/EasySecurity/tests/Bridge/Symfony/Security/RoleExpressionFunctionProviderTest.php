<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Bridge\Symfony\Security;

use EonX\EasySecurity\Bridge\Symfony\Exceptions\RoleConstantNotFoundException;
use EonX\EasySecurity\Bridge\Symfony\Security\RoleExpressionFunctionProvider;
use EonX\EasySecurity\Tests\AbstractTestCase;

final class RoleExpressionFunctionProviderTest extends AbstractTestCase
{
    public const ROLE_VALID = 'role';

    public function testRoleExpressionFunctionFound(): void
    {
        $function = (new RoleExpressionFunctionProvider([self::class]))->getFunctions()[0];

        self::assertEquals(self::ROLE_VALID, $function->getEvaluator()([], 'ROLE_VALID'));
        // Using cached permission
        self::assertEquals(self::ROLE_VALID, $function->getEvaluator()([], 'ROLE_VALID'));
    }

    public function testRoleExpressionFunctionNotFound(): void
    {
        $this->expectException(RoleConstantNotFoundException::class);

        $function = (new RoleExpressionFunctionProvider([self::class]))->getFunctions()[0];
        $function->getEvaluator()([], 'ROLE_INVALID');
    }
}
