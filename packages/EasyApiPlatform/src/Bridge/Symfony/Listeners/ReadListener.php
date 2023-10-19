<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Bridge\Symfony\Listeners;

use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Util\RequestAttributesExtractor;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ReadListener
{
    use OperationRequestInitiatorTrait;

    public function __construct(?ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory = null)
    {
        $this->resourceMetadataCollectionFactory = $resourceMetadataCollectionFactory;
    }

    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $operation = $this->initializeOperation($request);

        if (
            $operation?->getController() === 'api_platform.symfony.main_controller'
            || $request->attributes->get('_api_platform_disable_listeners')
        ) {
            return;
        }

        $attributes = RequestAttributesExtractor::extractAttributes($request);

        if (\count($attributes) === 0) {
            return;
        }

        if (
            (bool)($attributes['receive'] ?? false) === false ||
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
