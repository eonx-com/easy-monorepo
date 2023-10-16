<?php
declare(strict_types=1);

return [
    /**
     * Bugsnag API Key of your project.
     */
    'api_key' => \env('BUGSNAG_API_KEY'),

    'app_name' => [
        /**
         * Enable APP Name info in bugsnag.
         */
        'enabled' => false,

        /**
         * Env variable used to resolve APP name by default resolver.
         */
        'env_var' => 'APP_NAME',
    ],

    'aws_ecs_fargate' => [
        /**
         * Enable AWS ECS Fargate info in bugsnag.
         */
        'enabled' => false,

        /**
         * Filename to cache AWS ECS Fargate task metadata into.
         */
        'meta_storage_filename' => \storage_path('aws_ecs_fargate_meta.json'),

        /**
         * URL used to fetch AWS ECS Fargate task metadata.
         */
        'meta_url' => null,
    ],

    /**
     * Enable/Disable the entire package.
     */
    'enabled' => true,

    /**
     * List of handled exceptions that will set the bugsnag report as handled.
     */
    'handled_exceptions' => null,

    /**
     * Project root.
     */
    'project_root' => \base_path('app'),

    /**
     * Release stage.
     */
    'release_stage' => \env('APP_ENV'),

    'sensitive_data_sanitizer' => [
        /**
         * Enable sensitive data sanitization.
         */
        'enabled' => true,
    ],

    'session_tracking' => [
        /**
         * Expiry for sessions cache in seconds.
         */
        'cache_expires_after' => 3600,

        /**
         * Name of the cache store to use for session tracking.
         */
        'cache_store' => 'file',

        /**
         * Enable session tracking.
         */
        'enabled' => false,

        /**
         * List of URLs or Regex to exclude from session tracking.
         */
        'exclude_urls' => [],

        /**
         * Delimiter used in Regex to resolve excluded URLs.
         */
        'exclude_urls_delimiter' => '#',

        /**
         * Enable/Disable session tracking for queue jobs.
         */
        'queue_job_count_for_sessions' => false,
    ],

    /**
     * Strip path.
     */
    'strip_path' => \base_path(),

    /**
     * Enable/Disable default configurators.
     */
    'use_default_configurators' => true,
];
