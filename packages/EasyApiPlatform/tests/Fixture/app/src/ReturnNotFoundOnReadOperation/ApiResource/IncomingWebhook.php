<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Fixture\App\ReturnNotFoundOnReadOperation\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use EonX\EasyApiPlatform\Tests\Fixture\App\ReturnNotFoundOnReadOperation\Controller\IncomingWebhookController;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/incoming-webhooks/{someExtraVariable}',
            status: 204,
            controller: IncomingWebhookController::class,
            openapi: new OpenApiOperation(
                responses: [
                    204 => [
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                ],
                            ],
                        ],
                    ],
                ],
                parameters: [
                    [
                        'description' => 'Some extra variable',
                        'in' => 'path',
                        'name' => 'someExtraVariable',
                        'required' => true,
                        'schema' => ['type' => 'string'],
                    ],
                ]
            ),
            read: false,
        ),
        new Put(
            uriTemplate: '/incoming-webhooks/{someExtraVariable}',
            status: 204,
            controller: IncomingWebhookController::class,
            openapi: new OpenApiOperation(
                responses: [
                    204 => [
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                ],
                            ],
                        ],
                    ],
                ],
                parameters: [
                    [
                        'description' => 'Some extra variable',
                        'in' => 'path',
                        'name' => 'someExtraVariable',
                        'required' => true,
                        'schema' => ['type' => 'string'],
                    ],
                ]
            ),
            read: false,
        ),
    ]
)]
final class IncomingWebhook
{
}
