<?php
declare(strict_types=1);

namespace EonX\EasySwoole\EasyLogging\Processor;

use EonX\EasyLogging\Processor\AbstractSelfConfigProvidingProcessor;
use EonX\EasySwoole\Common\Enum\RequestAttribute;
use Monolog\LogRecord;
use Symfony\Component\HttpFoundation\RequestStack;

final class SwooleWorkerIdProcessor extends AbstractSelfConfigProvidingProcessor
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        $workerId = $this->requestStack->getCurrentRequest()
            ?->attributes->get(RequestAttribute::EasySwooleWorkerId->value);

        if (\is_int($workerId)) {
            $context = $record->context;
            $context['X-SWOOLE-WORKER-ID'] = $workerId;

            return $record->with(...['context' => $context]);
        }

        return $record;
    }
}
