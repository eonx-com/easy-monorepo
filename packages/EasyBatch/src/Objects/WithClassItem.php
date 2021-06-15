<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Objects;

use EonX\EasyBatch\Interfaces\BatchItemWithClassInterface;

class WithClassItem extends AbstractObjectDecorator implements BatchItemWithClassInterface
{
    /**
     * @var string
     */
    private $class;

    public function __construct(object $item, string $class)
    {
        $this->class = $class;

        parent::__construct($item);
    }

    public function getClass(): string
    {
        return $this->class;
    }
}
