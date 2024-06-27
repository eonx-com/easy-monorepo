<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyWebhook\Bundle\Enum\ConfigParam;
use EonX\EasyWebhook\Bundle\Enum\ConfigServiceId;
use EonX\EasyWebhook\Common\Middleware\SignatureHeaderMiddleware;
use EonX\EasyWebhook\Common\Signer\Rs256WebhookSigner;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Default RS256 Signer
    $services->set(Rs256WebhookSigner::class);

    $services
        ->set(SignatureHeaderMiddleware::class)
        ->arg('$signer', service(ConfigServiceId::Signer->value))
        ->arg('$secret', param(ConfigParam::Secret->value))
        ->arg('$signatureHeader', param(ConfigParam::SignatureHeader->value))
        ->arg('$priority', 100);
};
