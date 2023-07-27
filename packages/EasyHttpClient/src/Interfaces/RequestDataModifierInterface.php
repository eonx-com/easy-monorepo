<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Interfaces;

interface RequestDataModifierInterface
{
    public function modifyRequestData(RequestDataInterface $data): RequestDataInterface;
}
