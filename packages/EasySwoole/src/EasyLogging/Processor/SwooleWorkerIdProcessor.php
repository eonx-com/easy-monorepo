<?php
declare(strict_types=1);

namespace EonX\EasySwoole\EasyLogging\Processor;

use EonX\EasyLogging\Processor\AbstractSelfConfigProvidingProcessor;
use EonX\EasySwoole\Common\Enum\RequestAttribute;
use Symfony\Component\HttpFoundation\RequestStack;

final class SwooleWorkerIdProcessor extends AbstractSelfConfigProvidingProcessor
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
            ?->attributes->get(RequestAttribute::EasySwooleWorkerId->value);

        if (\is_int($workerId)) {
            $record['context']['X-SWOOLE-WORKER-ID'] = $workerId;
        }

        return $record;
    }
}
