<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Common\ValueObject;

interface BasicAuthInterface extends ApiTokenInterface
{
    public function getPassword(): string;

    public function getUsername(): string;
}
