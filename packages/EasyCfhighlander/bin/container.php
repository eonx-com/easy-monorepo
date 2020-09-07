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
/** @var string[] $configs */
$configs = \array_filter($configs);

$kernel = new CfhighlanderKernel();
$kernel->setConfigs($configs);
$kernel->boot();

return $kernel->getContainer();
