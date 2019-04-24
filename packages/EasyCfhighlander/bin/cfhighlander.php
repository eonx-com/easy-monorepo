#!/usr/bin/env php
<?php
declare(strict_types=1);

use LoyaltyCorp\EasyCfhighlander\Console\CfhighlanderApplication;

require_once __DIR__ . '/autoload.php';

/** @var \Symfony\Component\DependencyInjection\Container $container */
$container = require __DIR__ . '/container.php';

$app = $container->get(CfhighlanderApplication::class);
exit($app->run());
