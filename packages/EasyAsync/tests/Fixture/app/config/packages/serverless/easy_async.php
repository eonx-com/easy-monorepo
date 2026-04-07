<?php
declare(strict_types=1);

use Symfony\Config\EasyAsyncConfig;

return static function (EasyAsyncConfig $easyAsyncConfig): void {
    $easyAsyncConfig
        ->doctrine()
        ->closePersistentConnections()
        ->enabled(true)
        ->maxIdleTime(0.0);
};
