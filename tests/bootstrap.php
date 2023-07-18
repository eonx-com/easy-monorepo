<?php

declare(strict_types=1);

use DG\BypassFinals;
use PHP_CodeSniffer\Util\Tokens;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../vendor/squizlabs/php_codesniffer/autoload.php';
require_once __DIR__ . '/../vendor/symfony/symfony/src/Symfony/Component/DependencyInjection/Loader/PhpFileLoader.php';

// Enables mocking of final classes
// @see https://tomasvotruba.com/blog/2019/03/28/how-to-mock-final-classes-in-phpunit/
BypassFinals::enable();

new Tokens();
