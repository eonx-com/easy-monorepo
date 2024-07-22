<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Bundle\Enum;

enum ConfigServiceId: string
{
    case HttpClient = 'easy_http_client.http_client';

    case Logger = 'easy_http_client.logger';
}
