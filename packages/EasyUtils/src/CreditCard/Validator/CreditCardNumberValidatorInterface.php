<?php
declare(strict_types=1);

namespace EonX\EasyUtils\CreditCard\Validator;

interface CreditCardNumberValidatorInterface
{
    public function isCreditCardNumberValid(string $number): bool;
}
