<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\AwsRds\Provider;

use Aws\Credentials\CredentialProvider;
use Aws\Rds\AuthTokenGenerator;
use EonX\EasyDoctrine\AwsRds\Enum\AwsRdsOption;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class AwsRdsAuthTokenProvider
{
    private const CACHE_HASH_PATTERN = '%s_%s_%s_%s';

    private const CACHE_KEY_PATTERN = 'easy_doctrine.aws_rds_token.%s';

    private AuthTokenGenerator $authTokenGenerator;

    public function __construct(
        private readonly string $awsRegion,
        private readonly int $authTokenLifetimeInMinutes,
        private readonly CacheInterface $cache,
        private readonly ?LoggerInterface $logger = null,
    ) {
        $this->authTokenGenerator = new AuthTokenGenerator(CredentialProvider::defaultProvider());
    }

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getAuthToken(array $params): string
    {
        $region = $params['driverOptions'][AwsRdsOption::AwsRegion->value] ?? $this->awsRegion;
        $key = \sprintf(self::CACHE_KEY_PATTERN, \hash('xxh128', \sprintf(
            self::CACHE_HASH_PATTERN,
            $region,
            $params['host'],
            $params['port'],
            $params['user']
        )));

        return $this->cache->get($key, function (ItemInterface $item) use ($params, $region): string {
            $expiresAfter = ($this->authTokenLifetimeInMinutes * 60) - 30;

            $this->logger?->debug('Generating a new AWS RDS IAM auth token', [
                'expiresAfter' => $expiresAfter,
            ]);

            $item->expiresAfter($expiresAfter);

            $authToken = $this->authTokenGenerator->createToken(
                \sprintf('%s:%s', $params['host'], $params['port']),
                $region,
                $params['user'],
                $this->authTokenLifetimeInMinutes
            );

            $this->logger?->debug('The new AWS RDS IAM auth token has been generated');

            return $authToken;
        });
    }
}
