<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Batch;

use EonX\EasyAsync\Interfaces\Batch\BatchItemRequiresApprovalInterface;

final class BatchItemRequiresApprovalDecorator implements BatchItemRequiresApprovalInterface
{
    /**
     * @var object
     */
    private $item;

    public function __construct(object $item)
    {
        $this->item = $item;
    }

    public function getItem(): object
    {
        return $this->item;
    }
}
