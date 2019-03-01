<?php
declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Middleware Providers List
    |--------------------------------------------------------------------------
    |
    | Here you will define all your application's middleware providers to be registered
    | as services in the container.
    | Providers list must be an associative array where the keys are the
    | name of the pipeline the middleware list belongs to. Each middleware provider must implement
    | the StepTheFkUp\EasyPipeline\Interfaces\MiddlewareProviderInterface.
    |
    | Example:
    | 'pipeline-1' => \App\Pipelines\Providers\Pipeline1MiddlewareProvider::class,
    | 'pipeline-2' => \App\Pipelines\Providers\Pipeline2MiddlewareProvider::class,
    |
    */
    'providers' => [
        // Define your repositories here...
    ]
];
