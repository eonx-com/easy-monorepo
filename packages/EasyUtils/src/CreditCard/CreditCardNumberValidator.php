<?php

declare(strict_types=1);

namespace EonX\EasyUtils\CreditCard;

final class CreditCardNumberValidator
{
    private const CARD_TYPES = [

        // Debit cards must come first, since they have more specific patterns than their credit-card equivalents.
        'visaelectron' => [
            'type' => 'visaelectron',
            'pattern' => '/^4(026|17500|405|508|844|91[37])/',
            'length' => [16],
            'luhn' => true,
        ],
        'maestro' => [
            'type' => 'maestro',
            'pattern' => '/^(5(018|0[23]|[68])|6(39|7))/',
            'length' => [12, 13, 14, 15, 16, 17, 18, 19],
            'luhn' => true,
        ],
        'forbrugsforeningen' => [
            'type' => 'forbrugsforeningen',
            'pattern' => '/^600/',
            'length' => [16],
            'luhn' => true,
        ],
        'dankort' => [
            'type' => 'dankort',
            'pattern' => '/^5019/',
            'length' => [16],
            'luhn' => true,
        ],
        // Credit cards
        'visa' => [
            'type' => 'visa',
            'pattern' => '/^4/',
            'length' => [13, 16],
            'luhn' => true,
        ],
        'mastercard' => [
            'type' => 'mastercard',
            'pattern' => '/^(5[0-5]|2[2-7])/',
            'length' => [16],
            'luhn' => true,
        ],
        'amex' => [
            'type' => 'amex',
            'pattern' => '/^3[47]/',
            'format' => '/(\d{1,4})(\d{1,6})?(\d{1,5})?/',
            'length' => [15],
            'luhn' => true,
        ],
        'dinersclub' => [
            'type' => 'dinersclub',
            'pattern' => '/^3[0689]/',
            'length' => [14],
            'luhn' => true,
        ],
        'discover' => [
            'type' => 'discover',
            'pattern' => '/^6([045]|22)/',
            'length' => [16],
            'luhn' => true,
        ],
        'unionpay' => [
            'type' => 'unionpay',
            'pattern' => '/^(62|88)/',
            'length' => [16, 17, 18, 19],
            'luhn' => false,
        ],
        'jcb' => [
            'type' => 'jcb',
            'pattern' => '/^35/',
            'length' => [16],
            'luhn' => true,
        ],
    ];

    public function isCreditCardNumberValid(string $number): bool
    {
        // Strip non-numeric characters
        $number = preg_replace('/\D/', '', $number);

        $type = $this->getCreditCardType($number);

        return array_key_exists($type, self::CARD_TYPES) && $this->validateCard($number, $type);
    }

    private function getCreditCardType(string $number): string
    {
        foreach (self::CARD_TYPES as $type => $card) {
            if (preg_match($card['pattern'], $number)) {
                return $type;
            }
        }

        return '';
    }

    private function validateCard(string $number, string $type): bool
    {
        return $this->validatePattern($number, $type)
            && $this->validateLength($number, $type)
            && $this->validateLuhn($number, $type);
    }

    private function validatePattern(string $number, string $type): bool
    {
        return preg_match(self::CARD_TYPES[$type]['pattern'], $number) === 1;
    }

    private function validateLength(string $number, string $type): bool
    {
        foreach (self::CARD_TYPES[$type]['length'] as $length) {
            if (strlen($number) === $length) {
                return true;
            }
        }

        return false;
    }

    private function validateLuhn(string $number, string $type): bool
    {
        if (self::CARD_TYPES[$type]['luhn'] === false) {
            return true;
        }

        return $this->luhnCheck($number);
    }

    private function luhnCheck(string $number): bool
    {
        $checksum = 0;
        $numberLength = strlen($number);

        for ($i = (2-($numberLength % 2)); $i <= $numberLength; $i+=2) {
            $checksum += (int) ($number[$i-1]);
        }

        // Analyze odd digits in even length strings or even digits in odd length strings.
        for ($i = ($numberLength % 2) + 1; $i < $numberLength; $i += 2) {
            $digit = (int) ($number[$i-1]) * 2;
            if ($digit < 10) {
                $checksum += $digit;
            } else {
                $checksum += ($digit-9);
            }
        }

        return ($checksum % 10) === 0;
    }
}