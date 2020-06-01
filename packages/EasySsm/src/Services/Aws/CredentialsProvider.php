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

    public function __construct(AwsCredentialsProviderInterface $awsCredentialsProvider)
    {
        $this->awsCredentialsProvider = $awsCredentialsProvider;
    }

    /**
     * @return mixed[]
     */
    public function getCredentials(): array
    {
        $return = [
            'version' => $this->getCredential('AWS_VERSION', 'latest'),
            'region' => $this->getCredential('AWS_REGION', 'ap-southeast-2'),
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
        return (string)$this->getCredential('AWS_PROFILE');
    }

    /**
     * @return mixed
     */
    private function getCredential(string $env, ?string $default = null)
    {
        return \getenv($env) ?: $default;
    }
}
