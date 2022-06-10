<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\ErrorRenderer;

use EonX\EasyErrorHandler\Interfaces\ErrorDetailsResolverInterface;
use Symfony\Component\ErrorHandler\ErrorRenderer\ErrorRendererInterface;
use Symfony\Component\ErrorHandler\Exception\FlattenException;

final class TranslateInternalErrorMessageErrorRenderer implements ErrorRendererInterface
{
    /**
     * @var string[]
     */
    private const PATTERNS = [
        '<title>%s',
        '%s</h1>',
    ];

    public function __construct(
        private readonly ErrorDetailsResolverInterface $errorDetailsResolver,
        private readonly ErrorRendererInterface $decorated
    ) {
    }

    public function render(\Throwable $exception): FlattenException
    {
        $flattenException = $this->decorated->render($exception);

        $flattenException->setAsString(\str_replace(
            $this->resolvePatterns($exception->getMessage()),
            $this->resolvePatterns($this->errorDetailsResolver->resolveInternalMessage($exception)),
            $flattenException->getAsString()
        ));

        return $flattenException;
    }

    /**
     * @return string[]
     */
    private function resolvePatterns(string $value): array
    {
        return \array_map(static function (string $pattern) use ($value): string {
            return \sprintf($pattern, $value);
        }, self::PATTERNS);
    }
}
