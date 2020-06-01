<?php

declare(strict_types=1);

namespace EonX\EasySsm\Services\Aws;

use EonX\EasyAwsCredentialsFinder\Interfaces\AwsCredentialsProviderInterface;

final class CredentialsProvider implements CredentialsProviderInterface
{
    /**
     * @var \EonX\EasyAwsCredentialsFinder\Interfaces\AwsCredentialsProviderInterface
     */
    private $awsCredentialsProvider;

    /**
     * @var mixed[]
     */
    private $credentials;

    /**
     * @param null|mixed[] $credentials
     */
    public function __construct(AwsCredentialsProviderInterface $awsCredentialsProvider, ?array $credentials = null)
    {
        $this->awsCredentialsProvider = $awsCredentialsProvider;
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

        $awsCredentials = $this->awsCredentialsProvider->getCredentials();
        $credentials = [];
        $creds = ['key' => 'getAccessKeyId', 'secret' => 'getSecretKey', 'token' => 'getSessionToken'];

        foreach ($creds as $name => $getter) {
            if (empty($awsCredentials->{$getter}()) === false) {
                $credentials[$name] = $awsCredentials->{$getter}();
            }
        }

        if (empty($credentials) === false) {
            $return['credentials'] = $credentials;
        }

        $profile = $this->getProfile();

        if (isset($return['credentials']) === false && empty($profile) === false) {
            $return['profile'] = $profile;
        }

        return $return;
    }

    public function getProfile(): string
    {
        return (string)$this->getCredential('profile', 'AWS_PROFILE');
    }

    /**
     * @return mixed
     */
    private function getCredential(string $name, string $env, ?string $default = null)
    {
        return $this->credentials[$name] ?? \getenv($env) ?: $default;
    }
}
