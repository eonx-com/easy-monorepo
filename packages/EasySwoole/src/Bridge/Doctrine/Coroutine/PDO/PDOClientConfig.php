<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Doctrine\Coroutine\PDO;

use EonX\EasyDoctrine\AwsRds\Resolver\AwsRdsConnectionParamsResolver;
use OpenSwoole\Core\Coroutine\Client\ClientConfigInterface;
use Psr\Log\LoggerInterface;

final readonly class PDOClientConfig implements ClientConfigInterface
{
    public function __construct(
        private array $params,
        private ?AwsRdsConnectionParamsResolver $connectionParamsResolver = null,
        private ?LoggerInterface $logger = null,
    ) {
    }

    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getParams(): array
    {
        return $this->connectionParamsResolver?->getParams($this->params) ?? $this->params;
    }
}
