<?php

declare(strict_types=1);

namespace EonX\EasyWebhooks\Interfaces;

use Symfony\Contracts\HttpClient\HttpClientInterface;

interface HttpClientFactoryInterface
{
    public function create(): HttpClientInterface;
}
