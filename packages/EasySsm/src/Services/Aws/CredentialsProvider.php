<?php

declare(strict_types=1);

namespace EonX\EasySsm\Services\Aws;

final class CredentialsProvider implements CredentialsProviderInterface
{
    /**
     * @var mixed[]
     */
    private $credentials;

    /**
     * @param null|mixed[] $credentials
     */
    public function __construct(?array $credentials = null)
    {
        $this->credentials = $credentials ?? [];
    }

    /**
     * @return mixed[]
     */
    public function getCredentials(): array
    {
        $return = [
            'version' => $this->getCredential('version', 'AWS_VERSION', 'latest'),
            'region' => $this->getCredential('region', 'AWS_REGION', 'ap-southeast-2'),
        ];

        $credentials = [];
        $creds = ['key' => 'AWS_KEY', 'secret' => 'AWS_SECRET', 'token' => 'AWS_TOKEN'];

        foreach ($creds as $name => $env) {
            $value = $this->getCredential($name, $env);

            if (empty($value) === false) {
                $credentials[$name] = $value;
            }
        }

        if (empty($credentials) === false) {
            $return['credentials'] = $credentials;
        }

        if (isset($credentials['key']) === false || isset($credentials['secret']) === false) {
            $return['profile'] = $this->getProfile();
        }

        return $return;
    }

    public function getProfile(): string
    {
        return (string)$this->getCredential('profile', 'AWS_PROFILE', 'default');
    }

    /**
     * @return mixed
     */
    private function getCredential(string $name, string $env, ?string $default = null)
    {
        return $this->credentials[$name] ?? \getenv($env) ?: $default;
    }
}
