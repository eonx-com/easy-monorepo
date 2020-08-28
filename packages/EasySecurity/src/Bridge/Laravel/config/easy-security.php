<?php

declare(strict_types=1);

use EonX\EasySecurity\Interfaces\SecurityContextInterface;

return [
    'context_service_id' => SecurityContextInterface::class,
    'token_decoder' => null,
];
