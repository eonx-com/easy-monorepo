<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Interfaces;

use Symfony\Contracts\HttpClient\HttpClientInterface;

interface HttpClientFactoryInterface
{
    public function create(): HttpClientInterface;
}
