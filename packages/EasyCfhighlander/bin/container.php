<?php
declare(strict_types=1);

use EonX\EasyCfhighlander\HttpKernel\CfhighlanderKernel;
use Symfony\Component\Console\Input\ArgvInput;
use Symplify\SetConfigResolver\ConfigResolver;

$configs = [];

// Get config
$inputConfig = (new ConfigResolver())->resolveFromInputWithFallback(new ArgvInput(), [
    'easy-cfhighlander.yaml',
    'easy-cfhighlander.yml',
]);

if ($inputConfig) {
    $configs[] = $inputConfig;
}

$kernel = new CfhighlanderKernel();
$kernel->setConfigs(\array_filter($configs));
$kernel->boot();

return $kernel->getContainer();
