<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Common\Modifier;

use EonX\EasyHttpClient\Common\ValueObject\RequestData;

interface RequestDataModifierInterface
{
    public function modifyRequestData(RequestData $data): RequestData;
}
