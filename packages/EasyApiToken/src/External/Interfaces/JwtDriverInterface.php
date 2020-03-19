<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\External\Interfaces;

interface JwtDriverInterface
{
    /**
     * @return mixed
     */
    public function decode(string $token);

    /**
     * @param mixed $input
     */
    public function encode($input): string;
}
