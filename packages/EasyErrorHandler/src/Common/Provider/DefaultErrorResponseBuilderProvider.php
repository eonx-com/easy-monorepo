<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Provider;

use EonX\EasyErrorHandler\Common\Builder\CodeErrorResponseBuilder;
use EonX\EasyErrorHandler\Common\Builder\ExtendedExceptionErrorResponseBuilder;
use EonX\EasyErrorHandler\Common\Builder\HttpExceptionErrorResponseBuilder;
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
    private const KEY_EXTENDED_EXCEPTION_KEYS = 'extended_exception_keys';

    public function __construct(
        private ErrorDetailsResolverInterface $errorDetailsResolver,
        private TranslatorInterface $translator,
        private array $keys,
    ) {
    }

    /**
     * @return iterable<\EonX\EasyErrorHandler\Common\Builder\ErrorResponseBuilderInterface>
     */
    public function getBuilders(): iterable
    {
        yield new CodeErrorResponseBuilder($this->getKey(CodeErrorResponseBuilder::DEFAULT_KEY));
        yield new ExtendedExceptionErrorResponseBuilder(
            $this->errorDetailsResolver,
            $this->translator,
            $this->keys[self::KEY_EXTENDED_EXCEPTION_KEYS] ?? [],
            $this->getKey(ExtendedExceptionErrorResponseBuilder::DEFAULT_KEY)
        );
        yield new StatusCodeErrorResponseBuilder();
        yield new SubCodeErrorResponseBuilder($this->getKey(SubCodeErrorResponseBuilder::DEFAULT_KEY));
        yield new TimeErrorResponseBuilder($this->getKey(TimeErrorResponseBuilder::DEFAULT_KEY));
        yield new UserMessageErrorResponseBuilder(
            $this->translator,
            $this->getKey(UserMessageErrorResponseBuilder::DEFAULT_KEY)
        );
        yield new ViolationsErrorResponseBuilder($this->getKey(ViolationsErrorResponseBuilder::DEFAULT_KEY));

        if (\interface_exists(HttpExceptionInterface::class)) {
            yield new HttpExceptionErrorResponseBuilder($this->keys);
        }
    }

    private function getKey(string $name): string
    {
        return $this->keys[$name] ?? $name;
    }
}
