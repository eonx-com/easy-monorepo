<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->alias(SerializerInterface::class, 'messenger.transport.native_php_serializer');

    $containerConfigurator->extension('framework', [
        'messenger' => true,
        // The tests do not use the Serializer. When it is enabled, the container fails to compile in the monorepo,
        // where the Validator component is installed but not enabled: the "argument_resolver.request_payload" service
        // then references the missing "validator.translation_domain" parameter
        'serializer' => [
            'enabled' => false,
        ],
        'test' => true,
    ]);
};
