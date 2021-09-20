<?php

declare(strict_types=1);

return [
    /**
     * Enable bridge with EasyErrorHandler to report queue worker stopping with non-zero status.
     */
    'easy_error_handler_worker_stopping_enabled' => true,

    'implementation' => 'doctrine',
    'jobs_table' => 'easy_async_jobs',
    'job_logs_table' => 'easy_async_job_logs',
];
