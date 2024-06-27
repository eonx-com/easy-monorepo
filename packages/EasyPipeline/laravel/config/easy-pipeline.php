<?php
declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Pipelines and Middleware Providers List
    |--------------------------------------------------------------------------
    |
    | Here you will define all your application pipelines and associated middleware providers to be registered
    | as services in the container.
    | Pipelines list must be an associative array where the keys are the
    | name of the pipeline the middleware provider belongs to. Each middleware provider must implement
    | the EonX\EasyPipeline\Provider\MiddlewareProviderInterface.
    |
    | Example:
    | 'pipeline-1' => \App\Pipelines\Providers\Pipeline1MiddlewareProvider::class,
    | 'pipeline-2' => \App\Pipelines\Providers\Pipeline2MiddlewareProvider::class,
    |
    */
    'pipelines' => [
        // Define your repositories here...
    ],
];
