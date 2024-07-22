<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Fixture\App\EasyErrorHandler\Enum;

use EonX\EasyErrorHandler\ErrorCodes\Attribute\AsErrorCodes;

#[AsErrorCodes]
enum ErrorCode: int
{
    case Code1 = 1;
}
