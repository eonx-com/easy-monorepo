<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Common\ValueObject;

interface ApiKeyInterface extends ApiTokenInterface
{
    public function getApiKey(): string;
}
