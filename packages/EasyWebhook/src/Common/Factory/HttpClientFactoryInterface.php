<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Factory;

use Symfony\Contracts\HttpClient\HttpClientInterface;

interface HttpClientFactoryInterface
{
    public function create(): HttpClientInterface;
}
