<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyActivity\Tests\Fixture\App\Entity\User;
use Symfony\Config\EasyActivityConfig;

return static function (EasyActivityConfig $easyActivityConfig): void {
    // Configuration for testing encryptable fields masking integration
    $easyActivityConfig
        ->disallowedProperties([
            'email',
        ]);
    $easyActivityConfig->subjects(User::class)
        ->type('User')
        ->disallowedProperties([
            'password',
        ]);
};
