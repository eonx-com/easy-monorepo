<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Common\Driver;

interface JwtDriverInterface
{
    public function decode(string $token): mixed;

    public function encode(array|object $input): string;
}
