<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler;

use EonX\EasyErrorHandler\Interfaces\ErrorDetailsResolverInterface;
use EonX\EasyErrorHandler\Interfaces\Exceptions\StatusCodeAwareExceptionInterface;
use EonX\EasyErrorHandler\Interfaces\Exceptions\SubCodeAwareExceptionInterface;
use EonX\EasyErrorHandler\Interfaces\Exceptions\TranslatableExceptionInterface;
use EonX\EasyErrorHandler\Interfaces\Exceptions\ValidationExceptionInterface;
use EonX\EasyUtils\Helpers\ErrorDetailsHelper;
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

        return $this->doResolvePreviousDetails(
            $this->doResolveExtendedDetails($throwable),
            $throwable,
            1,
            $maxDepth ?? self::DEFAULT_MAX_DEPTH
        );
    }

    /**
     * @return mixed[]
     */
    public function resolveSimpleDetails(\Throwable $throwable, ?bool $withTrace = null): array
    {
        return ErrorDetailsHelper::resolveSimpleDetails($throwable, $withTrace);
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
    private function doResolveExtendedDetails(\Throwable $throwable, ?bool $withTrace = null): array
    {
        $details = $this->resolveSimpleDetails($throwable, $withTrace);

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

    /**
     * @param mixed[] $previousDetails
     * @param \Throwable $throwable
     * @param int $depth
     * @param int $maxDepth
     *
     * @return mixed[]
     */
    private function doResolvePreviousDetails(
        array $previousDetails,
        \Throwable $throwable,
        int $depth,
        int $maxDepth
    ): array {
        $this->chain[] = \spl_object_hash($throwable);

        $previous = $throwable->getPrevious();
        if ($previous !== null && $this->canResolvePrevious($previous, $maxDepth, $depth)) {
            $previousDetails[\sprintf('previous_%d', $depth)] = $this->doResolveExtendedDetails($previous, false);

            return $this->doResolvePreviousDetails($previousDetails, $previous, $depth + 1, $maxDepth);
        }

        return $previousDetails;
    }
}
