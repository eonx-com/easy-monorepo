<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\EasyLogging;

use EonX\EasyLogging\Config\AbstractSelfProcessorConfigProvider;
use EonX\EasySwoole\Interfaces\RequestAttributesInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final class SwooleWorkerIdProcessorConfigProvider extends AbstractSelfProcessorConfigProvider
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    /**
     * @param array $record
     *
     * @return array
     */
    public function __invoke(array $record): array
    {
        $workerId = $this->requestStack->getCurrentRequest()
            ?->attributes->get(RequestAttributesInterface::EASY_SWOOLE_WORKER_ID);

        if (\is_int($workerId)) {
            $record['context']['X-SWOOLE-WORKER-ID'] = $workerId;
        }

        return $record;
    }
}
