<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\External;

use Composer\InstalledVersions;

/**
 * @deprecated compatibility layer, will be removed in 5.0
 * @internal
 */
trait FirebaseJwtVersionTrait
{
    private static bool $isFirebaseJwtV6;

    /**
     * @deprecated compatibility layer, will be removed in EasyApiToken 5.0
     * @internal
     */
    private static function isFirebaseJwtV6(): bool
    {
        if (isset(self::$isFirebaseJwtV6) === false) {
            $packageVersion = (string)InstalledVersions::getVersion('firebase/php-jwt');

            self::$isFirebaseJwtV6 = \version_compare($packageVersion, '6.0.0') >= 0;
        }

        return self::$isFirebaseJwtV6;
    }
}
