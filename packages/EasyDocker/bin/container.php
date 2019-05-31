<?php
declare(strict_types=1);

use LoyaltyCorp\EasyDocker\HttpKernel\EasyDockerKernel;
use Symfony\Component\Console\Input\ArgvInput;
use Symplify\PackageBuilder\Configuration\ConfigFileFinder;

$configName = 'easy-docker.yaml';
$configFallback = ['easy-docker.yml'];
$configs = [];

// Get config
ConfigFileFinder::detectFromInput($configName, new ArgvInput());
$configs[] = ConfigFileFinder::provide($configName, $configFallback);

$kernel = new EasyDockerKernel();
$kernel->setConfigs(\array_filter($configs));
$kernel->boot();

return $kernel->getContainer();
