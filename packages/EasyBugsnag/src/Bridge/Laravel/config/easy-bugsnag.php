<?php

declare(strict_types=1);

return [
    /**
     * Bugsnag API Key of your project.
     */
    'api_key' => \env('BUGSNAG_API_KEY'),

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
