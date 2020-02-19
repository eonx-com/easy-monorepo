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
    'log_queue_worker_stop' => \env('EASY_CORE_LOG_QUEUE_WORKER_STOP', true)
];
