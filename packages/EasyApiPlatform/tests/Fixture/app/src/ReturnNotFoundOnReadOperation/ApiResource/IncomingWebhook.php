<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Fixture\App\ReturnNotFoundOnReadOperation\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use EonX\EasyApiPlatform\Tests\Fixture\App\ReturnNotFoundOnReadOperation\Controller\IncomingWebhookController;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/incoming-webhooks/{someExtraVariable}',
            status: 204,
            controller: IncomingWebhookController::class,
            openapiContext: [
                'parameters' => [
                    [
                        'description' => 'Some extra variable',
                        'in' => 'path',
                        'name' => 'someExtraVariable',
                        'required' => true,
                        'schema' => ['type' => 'string'],
                    ],
                ],
                'responses' => [
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
            ],
            read: false,
        ),
    ]
)]
final class IncomingWebhook
{
}
