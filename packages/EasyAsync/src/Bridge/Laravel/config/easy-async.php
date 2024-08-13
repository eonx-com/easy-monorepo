<?php
declare(strict_types=1);

return [
    /**
     * Add a listener to log when a queue worker stops.
     */
    'log_queue_worker_stop' => \env('EASY_ASYNC_LOG_QUEUE_WORKER_STOP', true),

    /**
     * Enable bridge with EasyErrorHandler to report queue worker stopping with non-zero status.
     */
    'easy_error_handler_worker_stopping_enabled' => true,

    'implementation' => 'doctrine',
    'job_logs_table' => 'easy_async_job_logs',
    'jobs_table' => 'easy_async_jobs',
];
