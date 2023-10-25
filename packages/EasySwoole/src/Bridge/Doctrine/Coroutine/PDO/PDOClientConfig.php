<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Doctrine\Coroutine\PDO;

use EonX\EasyDoctrine\Bridge\AwsRds\AwsRdsConnectionParamsResolver;
use OpenSwoole\Core\Coroutine\Client\ClientConfigInterface;
use Psr\Log\LoggerInterface;

final class PDOClientConfig implements ClientConfigInterface
{
    public function __construct(
        private readonly array $params,
        private readonly ?AwsRdsConnectionParamsResolver $connectionParamsResolver = null,
        private readonly ?LoggerInterface $logger = null,
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
