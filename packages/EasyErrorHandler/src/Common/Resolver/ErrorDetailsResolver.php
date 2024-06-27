<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Resolver;

use EonX\EasyErrorHandler\Common\Exception\StatusCodeAwareExceptionInterface;
use EonX\EasyErrorHandler\Common\Exception\SubCodeAwareExceptionInterface;
use EonX\EasyErrorHandler\Common\Exception\TranslatableExceptionInterface;
use EonX\EasyErrorHandler\Common\Exception\ValidationExceptionInterface;
use EonX\EasyErrorHandler\Common\Translator\TranslatorInterface;
use EonX\EasyUtils\Common\Helper\ErrorDetailsHelper;
use Psr\Log\LoggerInterface;
use Throwable;

final class ErrorDetailsResolver implements ErrorDetailsResolverInterface
{
    private const DEFAULT_INTERNAL_MESSAGES_LOCALE = 'en';

    /**
     * @var string[]
     */
    private array $chain = [];

    /**
     * @var string[]
     */
    private array $internalMessages = [];

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly TranslatorInterface $translator,
        private readonly bool $translateInternalMessages = false,
        private readonly string $internalMessagesLocale = self::DEFAULT_INTERNAL_MESSAGES_LOCALE,
    ) {
    }

    public function reset(): void
    {
        $this->internalMessages = [];
    }

    public function resolveExtendedDetails(Throwable $throwable, ?int $maxDepth = null): array
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

    public function resolveInternalMessage(Throwable $throwable): string
    {
        $errorIdentifier = $this->resolveErrorIdentifier($throwable);

        if (isset($this->internalMessages[$errorIdentifier])) {
            return $this->internalMessages[$errorIdentifier];
        }

        $message = $throwable->getMessage();
        if ($this->translateInternalMessages && $throwable instanceof TranslatableExceptionInterface) {
            $message = $this->translator->trans(
                $message,
                $throwable->getMessageParams(),
                $this->internalMessagesLocale
            );
        }

        return $this->internalMessages[$errorIdentifier] = $message;
    }

    public function resolveSimpleDetails(Throwable $throwable, ?bool $withTrace = null): array
    {
        return ErrorDetailsHelper::resolveSimpleDetails($throwable, $withTrace);
    }

    private function canResolvePrevious(Throwable $previous, int $maxDepth, int $depth): bool
    {
        if (\in_array($this->resolveErrorIdentifier($previous), $this->chain, true)) {
            $this->logger->info('Circular reference detected in throwable chain', [
                'exception' => $this->resolveSimpleDetails($previous),
            ]);

            return false;
        }

        return $maxDepth === -1 || $depth < $maxDepth;
    }

    private function doResolveExtendedDetails(Throwable $throwable, ?bool $withTrace = null): array
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

    private function doResolvePreviousDetails(
        array $previousDetails,
        Throwable $throwable,
        int $depth,
        int $maxDepth,
    ): array {
        $this->chain[] = $this->resolveErrorIdentifier($throwable);

        $previous = $throwable->getPrevious();
        if ($previous !== null && $this->canResolvePrevious($previous, $maxDepth, $depth)) {
            $previousDetails[\sprintf('previous_%d', $depth)] = $this->doResolveExtendedDetails($previous, false);

            return $this->doResolvePreviousDetails($previousDetails, $previous, $depth + 1, $maxDepth);
        }

        return $previousDetails;
    }

    private function resolveErrorIdentifier(Throwable $throwable): string
    {
        return \spl_object_hash($throwable);
    }
}
