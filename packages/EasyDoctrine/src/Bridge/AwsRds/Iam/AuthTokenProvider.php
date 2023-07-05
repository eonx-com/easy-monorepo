<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Bridge\AwsRds\Iam;

use Aws\Credentials\CredentialProvider;
use Aws\Rds\AuthTokenGenerator;
use EonX\EasyDoctrine\Bridge\AwsRds\AwsRdsOptionsInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class AuthTokenProvider
{
    private const CACHE_HASH_PATTERN = '%s_%s_%s_%s';

    private const CACHE_KEY_PATTERN = 'easy_doctrine.aws_rds_token.%s';

    private AuthTokenGenerator $authTokenGenerator;

    public function __construct(
        private readonly string $awsRegion,
        private readonly int $authTokenLifetimeInMinutes,
        private readonly CacheInterface $cache,
    ) {
        $this->authTokenGenerator = new AuthTokenGenerator(CredentialProvider::defaultProvider());
    }

    /**
     * @param mixed[] $params
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getAuthToken(array $params): string
    {
        $region = $params['driverOptions'][AwsRdsOptionsInterface::AWS_REGION] ?? $this->awsRegion;
        $key = \sprintf(self::CACHE_KEY_PATTERN, \hash('xxh128', \sprintf(
            self::CACHE_HASH_PATTERN,
            $region,
            $params['host'],
            $params['port'],
            $params['user']
        )));

        return $this->cache->get($key, function (ItemInterface $item) use ($params, $region): string {
            $item->expiresAfter(($this->authTokenLifetimeInMinutes * 60) - 30);

            return $this->authTokenGenerator->createToken(
                \sprintf('%s:%s', $params['host'], $params['port']),
                $region,
                $params['user'],
                $this->authTokenLifetimeInMinutes
            );
        });
    }
}
