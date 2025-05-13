<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Fixture\App\ReturnNotFoundOnReadOperation\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use EonX\EasyApiPlatform\Tests\Fixture\App\ReturnNotFoundOnReadOperation\Controller\IncomingWebhookController;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/incoming-webhooks/{someExtraVariable}',
            status: 204,
            controller: IncomingWebhookController::class,
            read: false,
        ),
        new Put(
            uriTemplate: '/incoming-webhooks/{someExtraVariable}',
            status: 204,
            controller: IncomingWebhookController::class,
            read: false,
        ),
    ],
    openapi: false,
)]
final class IncomingWebhook
{
}
