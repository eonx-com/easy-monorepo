<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Common\Checker;

/**
 * This allows to have different request limit per Swoole Worker.
 */
final class RequestLimitsChecker extends AbstractAppStateChecker
{
    private int $count = 0;

    private int $limit;

    /**
     * @throws \Exception
     */
    public function __construct(int $min, int $max, ?int $priority = null)
    {
        $this->limit = \random_int($min, $max);

        parent::__construct($priority);
    }

    public function isApplicationStateCompromised(): bool
    {
        $this->count++;

        return $this->count >= $this->limit;
    }
}
