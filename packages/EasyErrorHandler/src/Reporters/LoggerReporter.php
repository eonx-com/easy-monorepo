<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Reporters;

use EonX\EasyErrorHandler\Helpers\ErrorDetailsHelper;
use EonX\EasyErrorHandler\Interfaces\Exceptions\LogLevelAwareExceptionInterface;
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
            $this->getLevel($throwable),
            $throwable->getMessage(),
            ['exception' => ErrorDetailsHelper::getDetails($throwable)]
        );
    }

    private function getLevel(Throwable $throwable): int
    {
        if ($throwable instanceof LogLevelAwareExceptionInterface) {
            return $throwable->getLogLevel();
        }

        return 400;
    }
}
