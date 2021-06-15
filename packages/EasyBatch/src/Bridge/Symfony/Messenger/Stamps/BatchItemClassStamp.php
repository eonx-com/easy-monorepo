<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony\Messenger\Stamps;

use Symfony\Component\Messenger\Stamp\StampInterface;

final class BatchItemClassStamp implements StampInterface
{
    /**
     * @var string
     */
    private $class;

    public function __construct(string $class)
    {
        $this->class = $class;
    }

    public function getClass(): string
    {
        return $this->class;
    }
}
