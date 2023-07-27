<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Constraints;

use Closure;
use EonX\EasyRandom\Interfaces\RandomStringConstraintInterface;

final class CallbackRandomStringConstraint implements RandomStringConstraintInterface
{
    private Closure $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback(...);
    }

    public function isValid(string $randomString): bool
    {
        return (bool)\call_user_func($this->callback, $randomString);
    }
}
