<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler;

use EonX\EasyErrorHandler\Interfaces\ErrorDetailsResolverInterface;
use EonX\EasyErrorHandler\Interfaces\Exceptions\StatusCodeAwareExceptionInterface;
use EonX\EasyErrorHandler\Interfaces\Exceptions\SubCodeAwareExceptionInterface;
use EonX\EasyErrorHandler\Interfaces\Exceptions\TranslatableExceptionInterface;
use EonX\EasyErrorHandler\Interfaces\Exceptions\ValidationExceptionInterface;
use Psr\Log\LoggerInterface;

final class ErrorDetailsResolver implements ErrorDetailsResolverInterface
{
    /**
     * @var string[]
     */
    private $chain;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return mixed[]
     */
    public function resolveExtendedDetails(\Throwable $throwable, ?int $maxDepth = null): array
    {
        // Reset throwable chain
        $this->chain = [];

        return $this->doResolve($throwable, $maxDepth ?? self::DEFAULT_MAX_DEPTH, 1);
    }

    /**
     * @return mixed[]
     */
    public function resolveSimpleDetails(\Throwable $throwable, ?bool $withTrace = null): array
    {
        $details = [
            'code' => $throwable->getCode(),
            'class' => \get_class($throwable),
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
            'message' => $throwable->getMessage(),
        ];

        if ($withTrace ?? true) {
            $details['trace'] = \array_map(static function (array $trace): array {
                unset($trace['args']);

                return $trace;
            }, $throwable->getTrace());
        }

        return $details;
    }

    private function canResolvePrevious(\Throwable $previous, int $maxDepth, int $depth): bool
    {
        if (\in_array(\spl_object_hash($previous), $this->chain, true)) {
            $this->logger->info('Circular reference detected in throwable chain', [
                'exception' => $this->resolveSimpleDetails($previous),
            ]);

            return false;
        }

        return $maxDepth === -1 || $depth < $maxDepth;
    }

    /**
     * @return mixed[]
     */
    private function doResolve(\Throwable $throwable, int $maxDepth, int $depth, ?bool $withTrace = null): array
    {
        $this->chain[] = \spl_object_hash($throwable);

        $details = $this->resolveSimpleDetails($throwable, $withTrace);

        $previous = $throwable->getPrevious();
        if ($previous !== null && $this->canResolvePrevious($previous, $maxDepth, $depth)) {
            $details[\sprintf('previous_%d', $depth)] = $this->doResolve($previous, $maxDepth, $depth + 1, false);
        }

        if ($throwable instanceof SubCodeAwareExceptionInterface) {
            $details['sub_code'] = $throwable->getSubCode();
        }

        if ($throwable instanceof StatusCodeAwareExceptionInterface) {
            $details['status_code'] = $throwable->getStatusCode();
        }

        if ($throwable instanceof TranslatableExceptionInterface) {
            $details['message_params'] = $throwable->getMessageParams();
            $details['user_message'] = $throwable->getUserMessage();
            $details['user_message_params'] = $throwable->getUserMessageParams();
        }

        if ($throwable instanceof ValidationExceptionInterface) {
            $details['violations'] = $throwable->getErrors();
        }

        return $details;
    }
}
