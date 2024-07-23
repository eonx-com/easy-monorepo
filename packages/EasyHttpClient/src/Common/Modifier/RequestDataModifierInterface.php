<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Common\Modifier;

use EonX\EasyHttpClient\Common\ValueObject\RequestDataInterface;

interface RequestDataModifierInterface
{
    public function modifyRequestData(RequestDataInterface $data): RequestDataInterface;
}
