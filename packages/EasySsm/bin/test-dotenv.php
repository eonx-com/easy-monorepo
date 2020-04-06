<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

(new \EonX\EasySsm\Dotenv\SsmDotenv())->loadEnv('/commhub/dev/');

\var_dump($_ENV, $_SERVER);
