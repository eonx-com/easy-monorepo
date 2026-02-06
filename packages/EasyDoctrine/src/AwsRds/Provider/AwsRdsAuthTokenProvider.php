<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\AwsRds\Provider;

use Aws\Rds\AuthTokenGenerator;
use EonX\EasyDoctrine\AwsRds\Enum\AwsRdsOption;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final readonly class AwsRdsAuthTokenProvider implements AwsRdsAuthTokenProviderInterface
{
    private const string CACHE_HASH_PATTERN = '%s_%s_%s_%s';

    private const string CACHE_KEY_PATTERN = 'easy_doctrine.aws_rds_token.%s';

    public function __construct(
        private string $awsRegion,
        private int $authTokenLifetimeInMinutes,
        private AwsRdsAuthTokenCredentialsProviderInterface $awsRdsAuthTokenCredentialsProvider,
        private CacheInterface $cache,
        private ?LoggerInterface $logger = null,
    ) {
    }

    public function provide(array $params): string
    {
        $region = $params['driverOptions'][AwsRdsOption::Region->value] ?? $this->awsRegion;
        $key = \sprintf(self::CACHE_KEY_PATTERN, \hash('xxh128', \sprintf(
            self::CACHE_HASH_PATTERN,
            $region,
            $params['host'],
            $params['port'],
            $params['user']
        )));

        return $this->cache->get($key, function (ItemInterface $item) use ($params, $region): string {
            $expiresAfter = ($this->authTokenLifetimeInMinutes * 60) - 30;
            $item->expiresAfter($expiresAfter);

            $this->logger?->debug('Generating a new AWS RDS IAM auth token', [
                'expiresAfter' => $expiresAfter,
            ]);

            $authTokenGenerator = new AuthTokenGenerator($this->awsRdsAuthTokenCredentialsProvider->provide(
                $region,
                $params
            ));

            $authToken = $authTokenGenerator->createToken(
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
