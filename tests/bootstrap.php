<?php
declare(strict_types=1);

use PHP_CodeSniffer\Util\Tokens;

require_once __DIR__ . '/../.quality/vendor/autoload.php';
require_once __DIR__ . '/../.quality/vendor/squizlabs/php_codesniffer/autoload.php';
require_once __DIR__ . '/../.quality/vendor/symfony/dependency-injection/Loader/PhpFileLoader.php';

// enables mocking of final classes
// @see https://tomasvotruba.com/blog/2019/03/28/how-to-mock-final-classes-in-phpunit/
DG\BypassFinals::enable();

new Tokens();
