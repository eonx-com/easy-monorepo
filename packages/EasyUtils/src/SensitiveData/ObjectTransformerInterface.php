<?php

declare(strict_types=1);

namespace EonX\EasyUtils\SensitiveData;

use EonX\EasyUtils\Interfaces\HasPriorityInterface;

interface ObjectTransformerInterface extends HasPriorityInterface
{
    /**
     * @return mixed[]
     */
    public function transform(object $object): array;

    public function supports(object $object): bool;
}
