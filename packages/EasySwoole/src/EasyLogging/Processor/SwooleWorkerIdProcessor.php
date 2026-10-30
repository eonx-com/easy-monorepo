<?php
declare(strict_types=1);

namespace EonX\EasySwoole\EasyLogging\Processor;

use EonX\EasySwoole\Common\Enum\RequestAttribute;
use Monolog\Attribute\AsMonologProcessor;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsMonologProcessor]
final readonly class SwooleWorkerIdProcessor implements ProcessorInterface
{
    public function __construct(
        private RequestStack $requestStack,
    ) {}

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
