<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyWebhook\Bridge\BridgeConstantsInterface;
use EonX\EasyWebhook\Configurators\SignatureWebhookConfigurator;
use EonX\EasyWebhook\Signers\Rs256Signer;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Default RS256 Signer
    $services->set(Rs256Signer::class);

    $services
        ->set(SignatureWebhookConfigurator::class)
        ->arg('$signer', ref(BridgeConstantsInterface::SIGNER))
        ->arg('$secret', '%' . BridgeConstantsInterface::PARAM_SECRET . '%')
        ->arg('$signatureHeader', '%' . BridgeConstantsInterface::PARAM_SIGNATURE_HEADER . '%')
        ->arg('$priority', BridgeConstantsInterface::DEFAULT_CONFIGURATOR_PRIORITY + 1);
};
