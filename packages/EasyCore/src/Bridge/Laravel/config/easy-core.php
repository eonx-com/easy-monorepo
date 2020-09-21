<?php

declare(strict_types=1);

return [
    /**
     * Add a listener to clear doctrine entity manager before processing new job.
     */
    'clear_doctrine_em_before_job' => \env('EASY_CORE_CLEAR_DOCTRINE_EM_BEFORE_JOB', false),

    /**
     * Add a listener to log when a queue worker stops.
     */
    'log_queue_worker_stop' => \env('EASY_CORE_LOG_QUEUE_WORKER_STOP', true),

    /**
     * Add a listener to restart queue worker if doctrine entity manager is closed.
     */
    'restart_queue_on_doctrine_em_close' => \env('EASY_CORE_RESTART_QUEUE_ON_DOCTRINE_EM_CLOSE', true),

    /**
     * Enable/Configure search with elasticsearch.
     */
    'search' => [
        'enabled' => \env('EASY_CORE_SEARCH_ENABLED', false),
        'elasticsearch_host' => \env('ELASTICSEARCH_HOST', 'elasticsearch:9200'),
    ],

    /**
     * A list of array keys whose values will be ignored during processing.
     */
    'trim_strings' => [
        'except' => [],
    ],
];
