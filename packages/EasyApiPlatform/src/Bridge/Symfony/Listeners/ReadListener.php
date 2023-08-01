<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Bridge\Symfony\Listeners;

use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Util\OperationRequestInitiatorTrait;
use ApiPlatform\Util\RequestAttributesExtractor;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ReadListener
{
    use OperationRequestInitiatorTrait;

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $operation = $this->initializeOperation($request);
        $attributes = RequestAttributesExtractor::extractAttributes($request);

        if (\count($attributes) === 0) {
            return;
        }

        if (
            isset($attributes['receive']) === false ||
            $operation === null ||
            ($operation->canRead() ?? true) === false ||
            (\count((array)($operation->getUriVariables() ?? null)) === 0 && $request->isMethodSafe() === false)) {
            return;
        }

        if (
            $request->attributes->get('data') === null
            && $operation->getMethod() === HttpOperation::METHOD_POST
        ) {
            throw new NotFoundHttpException('Not Found');
        }
    }
}
