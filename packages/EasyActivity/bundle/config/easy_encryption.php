<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyActivity\Bundle\Enum\ConfigParam;
use EonX\EasyActivity\Common\Serializer\ActivitySubjectDataSerializerInterface;
use EonX\EasyActivity\Common\Serializer\EncryptedFieldMaskingSerializer;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    // Decorate the ActivitySubjectDataSerializerInterface to mask encrypted fields
    $services
        ->set(EncryptedFieldMaskingSerializer::class)
        ->decorate(ActivitySubjectDataSerializerInterface::class)
        ->arg('$decorated', service('.inner'))
        ->arg('$subjects', param(ConfigParam::Subjects->value));
};
