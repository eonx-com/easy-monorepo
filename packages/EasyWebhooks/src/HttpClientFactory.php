<?php

declare(strict_types=1);

namespace EonX\EasyWebhooks;

use EonX\EasyWebhooks\Interfaces\HttpClientFactoryInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class HttpClientFactory implements HttpClientFactoryInterface
{
    public function create(): HttpClientInterface
    {
        return HttpClient::create();
    }
}
