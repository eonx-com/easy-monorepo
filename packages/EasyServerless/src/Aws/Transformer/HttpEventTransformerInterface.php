<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Aws\Transformer;

interface HttpEventTransformerInterface
{
    public function transform(array $event): array;
}
