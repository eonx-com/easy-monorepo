<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Common\Factory;

interface IdFactoryInterface
{
    public function create(): int|string;
}
