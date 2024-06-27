<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Bundle\Enum;

enum ConfigParam: string
{
    case PermissionsLocations = 'easy_security.permissions_locations';

    case RolesLocations = 'easy_security.roles_locations';

    case TokenDecoder = 'easy_security.token_decoder';
}
