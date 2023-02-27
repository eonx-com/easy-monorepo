<?php

declare(strict_types=1);

namespace EonX\EasyUtils\CreditCard;

interface CreditCardNumberValidatorInterface
{
    public function isCreditCardNumberValid(string $number): bool;
}
