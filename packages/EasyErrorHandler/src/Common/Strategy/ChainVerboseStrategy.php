<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Strategy;

use EonX\EasyErrorHandler\Common\Driver\VerboseStrategyDriverInterface;
use EonX\EasyUtils\Helpers\CollectorHelper;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

final class ChainVerboseStrategy implements VerboseStrategyInterface
{
    /**
     * @var \EonX\EasyErrorHandler\Common\Driver\VerboseStrategyDriverInterface[]
     */
    private readonly array $drivers;

    private bool $verbose;

    /**
     * @param iterable<\EonX\EasyErrorHandler\Common\Driver\VerboseStrategyDriverInterface> $drivers
     */
    public function __construct(iterable $drivers, ?bool $defaultIsVerbose = null)
    {
        $this->drivers = CollectorHelper::orderLowerPriorityFirstAsArray(
            CollectorHelper::filterByClass($drivers, VerboseStrategyDriverInterface::class)
        );
        $this->verbose = $defaultIsVerbose ?? false;
    }

    public function isVerbose(): bool
    {
        return $this->verbose;
    }

    public function setThrowable(Throwable $throwable, ?Request $request = null): VerboseStrategyInterface
    {
        foreach ($this->drivers as $driver) {
            $isVerbose = $driver->isVerbose($throwable, $request);

            if ($isVerbose !== null) {
                $this->verbose = $isVerbose;

                break;
            }
        }

        return $this;
    }
}
