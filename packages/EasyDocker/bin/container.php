<?php
declare(strict_types=1);

use EonX\EasyDocker\HttpKernel\EasyDockerKernel;
use Symfony\Component\Console\Input\ArgvInput;
use Symplify\SetConfigResolver\ConfigResolver;

$configs = [];

// Get config
$inputConfig = (new ConfigResolver())->resolveFromInputWithFallback(new ArgvInput(), [
    'easy-docker.yaml',
    'easy-docker.yml'
]);

if ($inputConfig) {
    $configs[] = $inputConfig;
}

$kernel = new EasyDockerKernel();
$kernel->setConfigs(\array_filter($configs));
$kernel->boot();

return $kernel->getContainer();
