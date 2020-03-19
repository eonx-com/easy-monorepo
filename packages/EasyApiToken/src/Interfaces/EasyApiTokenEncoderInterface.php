<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces;

interface EasyApiTokenEncoderInterface
{
    public function encode(EasyApiTokenInterface $apiToken): string;
}
