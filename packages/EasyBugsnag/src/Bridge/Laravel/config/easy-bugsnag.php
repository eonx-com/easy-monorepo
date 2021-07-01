<?php

declare(strict_types=1);

return [
    /**
     * Bugsnag API Key of your project.
     */
    'api_key' => \env('BUGSNAG_API_KEY'),

    /**
     * Enable AWS ECS Fargate info in bugsnag.
     */
    'aws_ecs_fargate' => false,

    /**
     * Filename to store AWS ECS Fargate meta, prevent requesting them each time.
     */
    'aws_ecs_fargate_meta_storage_filename' => '/var/www/storage/aws_ecs_fargate_meta.json',

    /**
     * URL to request AWS ECS Fargate meta from.
     */
    'aws_ecs_fargate_meta_url' => \sprintf('%s/task', \env('ECS_CONTAINER_METADATA_URI_V4')),

    /**
     * Enable Doctrine SQL Queries Breadcrumbs.
     */
    'doctrine_orm' => true,

    /**
     * Enable session tracking.
     */
    'session_tracking' => false,

    /**
     * List of Regex to exclude URLs from session tracking.
     */
    'session_tracking_exclude' => [],
];
