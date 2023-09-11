<?php
declare(strict_types=1);

use EonX\EasyWebhook\Signers\Rs256Signer;

return [
    'event' => [
        'enabled' => true,
        'event_header' => 'X-Webhook-Event',
    ],
    'id' => [
        'enabled' => true,
        'id_header' => 'X-Webhook-Id',
    ],
    'method' => 'POST',
    'send_async' => true,
    'signature' => [
        'enabled' => false,
        'secret' => 'easy-webhook-secret',
        'signature_header' => 'X-Webhook-Signature',
        'signer' => Rs256Signer::class,
    ],
    'use_default_middleware' => true,
];
