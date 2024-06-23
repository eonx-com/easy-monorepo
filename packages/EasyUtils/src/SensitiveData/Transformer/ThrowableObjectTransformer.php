<?php
declare(strict_types=1);

namespace EonX\EasyUtils\SensitiveData\Transformer;

use EonX\EasyUtils\Common\Helper\ErrorDetailsHelper;
use Throwable;

final class ThrowableObjectTransformer extends AbstractObjectTransformer
{
    public function supports(object $object): bool
    {
        return $object instanceof Throwable;
    }

    /**
     * @param \Throwable $object
     */
    public function transform(object $object): array
    {
        return ErrorDetailsHelper::resolveSimpleDetails($object);
    }
}
