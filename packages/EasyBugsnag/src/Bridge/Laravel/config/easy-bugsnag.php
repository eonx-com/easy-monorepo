<?php

declare(strict_types=1);

return [
    /**
     * Bugsnag API Key of your project.
     */
    'api_key' => \env('BUGSNAG_API_KEY'),

    'aws_ecs_fargate' => [
        /**
         * Enable AWS ECS Fargate info in bugsnag.
         */
        'enabled' => false,

        /**
         * URL used to fetch AWS ECS Fargate task metadata.
         */
        'meta_url' => \sprintf('%s/task', \env('ECS_CONTAINER_METADATA_URI_V4')),

        /**
         * Filename to cache AWS ECS Fargate task metadata into.
         */
        'meta_storage_filename' => \storage_path('aws_ecs_fargate_meta.json'),
    ],

    /**
     * Enable Doctrine SQL Queries Breadcrumbs.
     */
    'doctrine_orm' => true,

    'session_tracking' => [
        /**
         * Enable session tracking.
         */
        'enabled' => false,

        /**
         * Expiry for sessions cache in minutes.
         */
        'cache_expires_after' => 3600,

        /**
         * List of URLs or Regex to exclude from session tracking.
         */
        'session_tracking_exclude_urls' => [],

        /**
         * Delimiter used in Regex to resolve excluded URLs.
         */
        'session_tracking_exclude_urls_delimiter' => '#'
    ],

    /**
     * List of Regex to exclude URLs from session tracking.
     */
    'session_tracking_exclude' => [],
];
