<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Bridge;

interface BridgeConstantsInterface
{
    public const PARAM_DECODERS = 'easy_api_token.decoders';

    public const PARAM_DEFAULT_DECODER = 'easy_api_token.default_decoder';

    public const PARAM_DEFAULT_FACTORIES = 'easy_api_token.default_factories';

    public const TAG_DECODER_PROVIDER = 'easy_api_token.decoder_provider';
}
