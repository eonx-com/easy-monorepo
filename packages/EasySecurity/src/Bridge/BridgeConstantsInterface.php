<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge;

interface BridgeConstantsInterface
{
    /**
     * @var string
     */
    public const PARAM_CONTEXT_SERVICE_ID = 'easy_security.context_service_id';

    /**
     * @var string
     */
    public const PARAM_PERMISSIONS_LOCATIONS = 'easy_security.permissions_locations';

    /**
     * @var string
     */
    public const PARAM_TOKEN_DECODER = 'easy_security.token_decoder';

    /**
     * @var string
     */
    public const TAG_CONTEXT_CONFIGURATOR = 'easy_security.context_configurator';

    /**
     * @var string
     */
    public const TAG_CONTEXT_MODIFIER = 'easy_security.context_modifier';

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
    public const SERVICE_AUTHORIZATION_MATRIX_CACHE = 'easy_security.authorization_matrix_cache';
}
