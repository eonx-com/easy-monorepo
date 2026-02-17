<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyActivity\Bundle\Enum\ConfigParam;
use EonX\EasyActivity\Common\Serializer\ActivitySubjectDataSerializerInterface;
use EonX\EasyActivity\EasyEncryption\Serializer\EncryptableFieldMaskingSerializer;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    // Decorate the ActivitySubjectDataSerializerInterface to mask encryptable fields
    $services
        ->set(EncryptableFieldMaskingSerializer::class)
        ->decorate(ActivitySubjectDataSerializerInterface::class)
        ->arg('$decorated', service('.inner'))
        ->arg('$subjects', param(ConfigParam::Subjects->value));
};
