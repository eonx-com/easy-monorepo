<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Fixture\App\EasyErrorHandler\Enum;

enum Status: string
{
    case Active = 'active';

    case Inactive = 'inactive';
}
