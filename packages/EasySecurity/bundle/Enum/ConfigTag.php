<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Bundle\Enum;

enum ConfigTag: string
{
    case ContextConfigurator = 'easy_security.context_configurator';

    case PermissionsProvider = 'easy_security.permissions_provider';

    case RolesProvider = 'easy_security.roles_provider';

    case SecurityVoter = 'security.voter';
}
