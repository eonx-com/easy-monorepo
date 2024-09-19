<?php

/*
 * This file is part of the API Platform project.
 *
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace EonX\EasyApiPlatform\Common\StateProvider;

use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ReadStateProvider implements ProviderInterface
{
    public function __construct(
        private readonly ProviderInterface $decorated,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $data = $this->decorated->provide($operation, $uriVariables, $context);

        if ($data === null && $operation->getMethod() === HttpOperation::METHOD_POST) {
            throw new NotFoundHttpException('Not Found');
        }

        return $data;
    }
}
