<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\ErrorRenderer;

use EonX\EasyErrorHandler\Interfaces\ErrorDetailsResolverInterface;
use Symfony\Component\ErrorHandler\ErrorRenderer\ErrorRendererInterface;
use Symfony\Component\ErrorHandler\Exception\FlattenException;

final class TranslateInternalErrorMessageErrorRenderer implements ErrorRendererInterface
{
    public function __construct(
        private readonly ErrorDetailsResolverInterface $errorDetailsResolver,
        private readonly ErrorRendererInterface $decorated
    ) {
    }

    public function render(\Throwable $exception): FlattenException
    {
        $flattenException = $this->decorated->render($exception);

        $flattenException->setAsString(\str_replace(
            \sprintf('%s</h1>', $exception->getMessage()),
            \sprintf('%s</h1>', $this->errorDetailsResolver->resolveInternalMessage($exception)),
            $flattenException->getAsString()
        ));

        return $flattenException;
    }
}
