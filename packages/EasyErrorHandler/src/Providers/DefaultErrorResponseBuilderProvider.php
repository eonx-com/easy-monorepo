<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Providers;

use EonX\EasyErrorHandler\Builders\CodeErrorResponseBuilder;
use EonX\EasyErrorHandler\Builders\ExtendedExceptionErrorResponseBuilder;
use EonX\EasyErrorHandler\Builders\HttpExceptionErrorResponseBuilder;
use EonX\EasyErrorHandler\Builders\StatusCodeErrorResponseBuilder;
use EonX\EasyErrorHandler\Builders\SubCodeErrorResponseBuilder;
use EonX\EasyErrorHandler\Builders\TimeErrorResponseBuilder;
use EonX\EasyErrorHandler\Builders\UserMessageErrorResponseBuilder;
use EonX\EasyErrorHandler\Builders\ViolationsErrorResponseBuilder;
use EonX\EasyErrorHandler\Interfaces\ErrorDetailsResolverInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderProviderInterface;
use EonX\EasyErrorHandler\Interfaces\TranslatorInterface;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

final class DefaultErrorResponseBuilderProvider implements ErrorResponseBuilderProviderInterface
{
    private const KEY_EXTENDED_EXCEPTION_KEYS = 'extended_exception_keys';

    private readonly array $keys;

    public function __construct(
        private readonly ErrorDetailsResolverInterface $errorDetailsResolver,
        private readonly TranslatorInterface $translator,
        ?array $keys = null,
    ) {
        $this->keys = $keys ?? [];
    }

    /**
     * @return iterable<\EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderInterface>
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
