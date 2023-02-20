<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\External;

use Firebase\JWT\JWT as BaseJWT;
use ReflectionMethod;

/**
 * @deprecated compatibility layer, will be removed in 5.0
 * @internal
 */
trait JwtTrait
{
    private static bool $isFirebaseJwtV5;

    /**
     * @deprecated compatibility layer, will be removed in 5.0
     * @internal
     */
    private static function isFirebaseJwtV5(): bool
    {
        if (isset(self::$isFirebaseJwtV5) === false) {
            $methodParams = \array_map(
                static fn ($parameter) => $parameter->name,
                (new ReflectionMethod(BaseJWT::class, 'decode'))->getParameters()
            );

            self::$isFirebaseJwtV5 = \in_array('allowed_algs', $methodParams, true);
        }

        return self::$isFirebaseJwtV5;
    }
}
