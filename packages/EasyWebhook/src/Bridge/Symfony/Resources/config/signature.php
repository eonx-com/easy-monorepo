<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyWebhook\Bridge\BridgeConstantsInterface;
use EonX\EasyWebhook\Middleware\SignatureHeaderMiddleware;
use EonX\EasyWebhook\Signers\Rs256Signer;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Default RS256 Signer
    $services->set(Rs256Signer::class);

    $services
        ->set(SignatureHeaderMiddleware::class)
        ->arg('$signer', service(BridgeConstantsInterface::SIGNER))
        ->arg('$secret', '%' . BridgeConstantsInterface::PARAM_SECRET . '%')
        ->arg('$signatureHeader', '%' . BridgeConstantsInterface::PARAM_SIGNATURE_HEADER . '%')
        ->arg('$priority', 100);
};
