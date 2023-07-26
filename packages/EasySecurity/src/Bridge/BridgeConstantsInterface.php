<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge;

interface BridgeConstantsInterface
{
    public const LOG_CHANNEL = 'security';

    public const PARAM_PERMISSIONS_LOCATIONS = 'easy_security.permissions_locations';

    public const PARAM_ROLES_LOCATIONS = 'easy_security.roles_locations';

    public const PARAM_TOKEN_DECODER = 'easy_security.token_decoder';

    public const SERVICE_AUTHORIZATION_MATRIX_CACHE = 'easy_security.authorization_matrix_cache';

    public const SERVICE_LOGGER = 'easy_security.logger';

    public const TAG_CONTEXT_CONFIGURATOR = 'easy_security.context_configurator';

    public const TAG_PERMISSIONS_PROVIDER = 'easy_security.permissions_provider';

    public const TAG_ROLES_PROVIDER = 'easy_security.roles_provider';

    public const TAG_SECURITY_VOTER = 'security.voter';

    public const TAG_SECURITY_VOTER_PRIORITY = 100;
}
