<?php
declare(strict_types=1);

use PHP_CodeSniffer\Util\Tokens;

require_once __DIR__ . '/../.quality/vendor/autoload.php';
require_once __DIR__ . '/../.quality/vendor/squizlabs/php_codesniffer/autoload.php';
require_once __DIR__ . '/../.quality/vendor/symfony/dependency-injection/Loader/PhpFileLoader.php';

new Tokens();
