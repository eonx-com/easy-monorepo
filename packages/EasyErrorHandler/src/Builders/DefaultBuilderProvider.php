<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Builders;

use EonX\EasyErrorHandler\Interfaces\ErrorDetailsResolverInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderProviderInterface;
use EonX\EasyErrorHandler\Interfaces\TranslatorInterface;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

final class DefaultBuilderProvider implements ErrorResponseBuilderProviderInterface
{
    /**
     * @var \EonX\EasyErrorHandler\Interfaces\ErrorDetailsResolverInterface
     */
    private $errorDetailsResolver;

    /**
     * @var mixed[]
     */
    private $keys;

    /**
     * @var \EonX\EasyErrorHandler\Interfaces\TranslatorInterface
     */
    private $translator;

    /**
     * @param null|mixed[] $keys
     */
    public function __construct(
        ErrorDetailsResolverInterface $errorDetailsResolver,
        TranslatorInterface $translator,
        ?array $keys = null
    ) {
        $this->errorDetailsResolver = $errorDetailsResolver;
        $this->translator = $translator;
        $this->keys = $keys ?? [];
    }

    /**
     * @return iterable<\EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderInterface>
     */
    public function getBuilders(): iterable
    {
        yield new CodeBuilder($this->getKey('code'));
        yield new ExtendedExceptionBuilder(
            $this->errorDetailsResolver,
            $this->translator,
            $this->getKey('exception'),
            $this->keys['extended_exception_keys'] ?? []
        );
        yield new StatusCodeBuilder();
        yield new SubCodeBuilder($this->getKey('sub_code'));
        yield new TimeBuilder($this->getKey('time'));
        yield new UserMessageBuilder($this->translator, $this->getKey('message'));
        yield new ViolationsBuilder($this->getKey('violations'));

        if (\interface_exists(HttpExceptionInterface::class)) {
            yield new HttpExceptionBuilder($this->keys);
        }

        yield new ValidationErrorResponseBuilder();
    }

    private function getKey(string $name): string
    {
        return $this->keys[$name] ?? $name;
    }
}
