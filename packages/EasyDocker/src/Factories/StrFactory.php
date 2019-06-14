<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDocker\Factories;

use EoneoPay\Utils\Interfaces\StrInterface;
use EoneoPay\Utils\Str;

final class StrFactory
{
    /**
     * Create Str.
     *
     * @return \EoneoPay\Utils\Interfaces\StrInterface
     */
    public function create(): StrInterface
    {
        return new Str();
    }
}
