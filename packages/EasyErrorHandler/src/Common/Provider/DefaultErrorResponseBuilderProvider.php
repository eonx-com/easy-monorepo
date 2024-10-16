<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Provider;

use EonX\EasyErrorHandler\Common\Builder\CodeErrorResponseBuilder;
use EonX\EasyErrorHandler\Common\Builder\ExtendedExceptionErrorResponseBuilder;
use EonX\EasyErrorHandler\Common\Builder\HttpExceptionErrorResponseBuilder;
use EonX\EasyErrorHandler\Common\Builder\MessageErrorResponseBuilder;
use EonX\EasyErrorHandler\Common\Builder\StatusCodeErrorResponseBuilder;
use EonX\EasyErrorHandler\Common\Builder\SubCodeErrorResponseBuilder;
use EonX\EasyErrorHandler\Common\Builder\TimeErrorResponseBuilder;
use EonX\EasyErrorHandler\Common\Builder\UserMessageErrorResponseBuilder;
use EonX\EasyErrorHandler\Common\Builder\ViolationsErrorResponseBuilder;
use EonX\EasyErrorHandler\Common\Resolver\ErrorDetailsResolverInterface;
use EonX\EasyErrorHandler\Common\Translator\TranslatorInterface;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

final readonly class DefaultErrorResponseBuilderProvider implements ErrorResponseBuilderProviderInterface
{
    private const ERROR_RESPONSE_KEY_CODE = 'code';

    private const ERROR_RESPONSE_KEY_EXTENDED_EXCEPTION = 'exception';

    private const ERROR_RESPONSE_KEY_EXTENDED_EXCEPTION_KEYS = 'extended_exception_keys';

    private const ERROR_RESPONSE_KEY_MESSAGE = 'message';

    private const ERROR_RESPONSE_KEY_SUB_CODE = 'sub_code';

    private const ERROR_RESPONSE_KEY_TIME = 'time';

    private const ERROR_RESPONSE_KEY_VIOLATIONS = 'violations';

    public function __construct(
        private ErrorDetailsResolverInterface $errorDetailsResolver,
        private TranslatorInterface $translator,
        private array $keys,
        private ?array $exceptionMessages = null,
        private ?array $exceptionToCode = null,
    ) {
    }

    /**
     * @return iterable<\EonX\EasyErrorHandler\Common\Builder\ErrorResponseBuilderInterface>
     */
    public function getBuilders(): iterable
    {
        yield new CodeErrorResponseBuilder(
            $this->getKey(self::ERROR_RESPONSE_KEY_CODE),
            null,
            $this->exceptionToCode
        );
        yield new ExtendedExceptionErrorResponseBuilder(
            $this->errorDetailsResolver,
            $this->translator,
            $this->keys[self::ERROR_RESPONSE_KEY_EXTENDED_EXCEPTION_KEYS] ?? [],
            $this->getKey(self::ERROR_RESPONSE_KEY_EXTENDED_EXCEPTION)
        );
        yield new StatusCodeErrorResponseBuilder();
        yield new SubCodeErrorResponseBuilder($this->getKey(self::ERROR_RESPONSE_KEY_SUB_CODE));
        yield new TimeErrorResponseBuilder($this->getKey(self::ERROR_RESPONSE_KEY_TIME));
        yield new UserMessageErrorResponseBuilder(
            $this->translator,
            $this->getKey(self::ERROR_RESPONSE_KEY_MESSAGE)
        );
        yield new ViolationsErrorResponseBuilder($this->getKey(self::ERROR_RESPONSE_KEY_VIOLATIONS));

        if (\interface_exists(HttpExceptionInterface::class)) {
            yield new HttpExceptionErrorResponseBuilder($this->keys);
        }

        yield new MessageErrorResponseBuilder(
            $this->translator,
            $this->getKey(self::ERROR_RESPONSE_KEY_MESSAGE),
            null,
            $this->exceptionMessages
        );
    }

    private function getKey(string $key): string
    {
        return $this->keys[$key] ?? $key;
    }
}
