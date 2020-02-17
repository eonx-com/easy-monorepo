<?php
declare(strict_types=1);

use EonX\EasyCore\Bridge\Laravel\Providers\CachedConfigServiceProvider;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Laravel\Lumen\Application;
use Laravel\Lumen\Exceptions\Handler;

$app = new Application(__DIR__ . '/../');
$app->register(CachedConfigServiceProvider::class);
$app->bind(ExceptionHandler::class, Handler::class);

return $app;
