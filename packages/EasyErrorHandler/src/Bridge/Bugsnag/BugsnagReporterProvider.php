<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Bugsnag;

use Bugsnag\Client;
use EonX\EasyErrorHandler\Interfaces\ErrorReporterProviderInterface;

final class BugsnagReporterProvider implements ErrorReporterProviderInterface
{
    /**
     * @var \Bugsnag\Client
     */
    private $bugsnag;

    /**
     * @var null|int
     */
    private $threshold;

    public function __construct(Client $bugsnag, ?int $threshold = null)
    {
        $this->bugsnag = $bugsnag;
        $this->threshold = $threshold;
    }

    /**
     * @return iterable<\EonX\EasyErrorHandler\Interfaces\ErrorReporterInterface>
     */
    public function getReporters(): iterable
    {
        yield new BugsnagReporter($this->bugsnag, $this->threshold);
    }
}
