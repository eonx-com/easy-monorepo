<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Verbose;

use EonX\EasyErrorHandler\Interfaces\VerboseStrategyDriverInterface;
use EonX\EasyErrorHandler\Interfaces\VerboseStrategyInterface;
use EonX\EasyUtils\CollectorHelper;
use Symfony\Component\HttpFoundation\Request;

final class ChainVerboseStrategy implements VerboseStrategyInterface
{
    /**
     * @var \EonX\EasyErrorHandler\Interfaces\VerboseStrategyDriverInterface[]
     */
    private array $drivers;

    private bool $verbose;

    /**
     * @param iterable<\EonX\EasyErrorHandler\Interfaces\VerboseStrategyDriverInterface> $drivers
     */
    public function __construct(iterable $drivers, ?bool $defaultIsVerbose = null)
    {
        $this->drivers = CollectorHelper::orderLowerPriorityFirstAsArray(
            CollectorHelper::filterByClass($drivers, VerboseStrategyDriverInterface::class)
        );
        $this->verbose = $defaultIsVerbose ?? false;
    }

    public function setThrowable(\Throwable $throwable, ?Request $request = null): VerboseStrategyInterface
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

    public function isVerbose(): bool
    {
        return $this->verbose;
    }
}
