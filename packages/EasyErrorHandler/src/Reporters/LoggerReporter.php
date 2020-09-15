<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Reporters;

use EonX\EasyErrorHandler\Helpers\ErrorDetailsHelper;
use Psr\Log\LoggerInterface;
use Throwable;

final class LoggerReporter extends AbstractErrorReporter
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger, ?int $priority = null)
    {
        $this->logger = $logger;

        parent::__construct($priority);
    }

    /**
     * @return void|bool
     */
    public function report(Throwable $throwable)
    {
        $this->logger->log(
            $this->getLogLevel($throwable),
            $throwable->getMessage(),
            ['exception' => ErrorDetailsHelper::getDetails($throwable)]
        );
    }
}
