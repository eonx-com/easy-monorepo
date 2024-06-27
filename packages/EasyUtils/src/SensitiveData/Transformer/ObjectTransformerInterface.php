<?php
declare(strict_types=1);

namespace EonX\EasyUtils\SensitiveData\Transformer;

use EonX\EasyUtils\Common\Helper\HasPriorityInterface;

interface ObjectTransformerInterface extends HasPriorityInterface
{
    public function supports(object $object): bool;

    public function transform(object $object): array;
}
