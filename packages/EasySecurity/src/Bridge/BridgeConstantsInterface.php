<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge;

interface BridgeConstantsInterface
{
    /**
     * @var string
     */
    public const LOG_CHANNEL = 'security';

    /**
     * @var string
     */
    public const PARAM_PERMISSIONS_LOCATIONS = 'easy_security.permissions_locations';

    /**
     * @var string
     */
    public const PARAM_ROLES_LOCATIONS = 'easy_security.roles_locations';

    /**
     * @var string
     */
    public const PARAM_TOKEN_DECODER = 'easy_security.token_decoder';

    /**
     * @var string
     */
    public const SERVICE_AUTHORIZATION_MATRIX_CACHE = 'easy_security.authorization_matrix_cache';

    /**
     * @var string
     */
    public const SERVICE_LOGGER = 'easy_security.logger';

    /**
     * @var string
     */
    public const TAG_CONTEXT_CONFIGURATOR = 'easy_security.context_configurator';

    /**
     * @var string
     */
    public const TAG_PERMISSIONS_PROVIDER = 'easy_security.permissions_provider';

    /**
     * @var string
     */
    public const TAG_ROLES_PROVIDER = 'easy_security.roles_provider';

    /**
     * @var string
     */
    public const TAG_SECURITY_VOTER = 'security.voter';

    /**
     * @var int
     */
    public const TAG_SECURITY_VOTER_PRIORITY = 100;
}
