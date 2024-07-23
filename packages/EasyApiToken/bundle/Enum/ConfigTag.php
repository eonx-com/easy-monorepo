<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Bundle\Enum;

enum ConfigTag: string
{
    case DecoderProvider = 'easy_api_token.decoder_provider';
}
