<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Common\StateProvider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class ReadStateProvider implements ProviderInterface
{
    public function __construct(
        private ProviderInterface $decorated,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $data = $this->decorated->provide($operation, $uriVariables, $context);

        if ($data === null && $operation->canRead()) {
            throw new NotFoundHttpException('Not Found');
        }

        return $data;
    }
}
