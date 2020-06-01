<?php

declare(strict_types=1);

namespace EonX\EasyAwsCredentialsFinder\Finders;

use EonX\EasyAwsCredentialsFinder\AwsCredentials;
use EonX\EasyAwsCredentialsFinder\Interfaces\AwsCredentialsInterface;

final class EnvsCredentialsFinder extends AbstractAwsCredentialsFinder
{
    public function findCredentials(): ?AwsCredentialsInterface
    {
        $key = $this->getEnv('AWS_KEY');
        $secret = $this->getEnv('AWS_SECRET');
        $token = $this->getEnv('AWS_TOKEN');

        if (empty($key) || empty($secret)) {
            return null;
        }

        return new AwsCredentials($key, $secret, $token);
    }

    private function getEnv(string $key): ?string
    {
        if ($value = \getenv($key)) {
            return $value;
        }

        return $_SERVER[$key] ?? $_ENV[$key] ?? null;
    }
}
