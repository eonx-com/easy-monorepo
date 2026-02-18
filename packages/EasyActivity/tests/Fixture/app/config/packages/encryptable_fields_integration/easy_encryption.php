<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\EasyEncryptionConfig;

return static function (EasyEncryptionConfig $easyEncryptionConfig): void {
    $easyEncryptionConfig->defaultEncryptionKey('test-encryption-key-for-testing');
};
