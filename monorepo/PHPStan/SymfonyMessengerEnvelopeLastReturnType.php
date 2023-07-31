<?php
declare(strict_types=1);

namespace EonX\EasyMonorepo\PHPStan;

use InvalidArgumentException;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Symfony\Component\Messenger\Envelope;

final class SymfonyMessengerEnvelopeLastReturnType implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return Envelope::class;
    }

    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope,
    ): Type {
        $arg = $methodCall->args[0] ?? null;

        if ($arg instanceof Arg === false) {
            throw new InvalidArgumentException('Argument not found.');
        }

        $classname = $this->resolveClassName($arg->value);

        $secondType = \is_string($classname) ? new ObjectType($classname) : $scope->getType($arg->value);

        return new UnionType([new NullType(), $secondType]);
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'last';
    }

    private function resolveClassName(Expr $expr): ?string
    {
        if ($expr instanceof ClassConstFetch && $expr->class instanceof Name) {
            return $expr->class->toString();
        }

        return null;
    }
}
