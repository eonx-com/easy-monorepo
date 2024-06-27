<?php
declare(strict_types=1);

namespace EonX\EasyUtils\CreditCard\Validator;

final class CreditCardNumberValidator implements CreditCardNumberValidatorInterface
{
    private const AMEX = 'AMEX';

    private const CHINA_UNIONPAY = 'CHINA_UNIONPAY';

    private const DINERS = 'DINERS';

    private const DISCOVER = 'DISCOVER';

    private const INSTAPAYMENT = 'INSTAPAYMENT';

    private const JCB = 'JCB';

    private const LASER = 'LASER';

    private const MAESTRO = 'MAESTRO';

    private const MASTERCARD = 'MASTERCARD';

    private const MIR = 'MIR';

    private const SCHEMES = [
        // American Express card numbers start with 34 or 37 and have 15 digits
        self::AMEX => [
            '/^3[47][0-9]{13}$/',
        ],
        // China UnionPay cards start with 62 and have between 16 and 19 digits
        self::CHINA_UNIONPAY => [
            '/^62[0-9]{14,17}$/',
        ],
        // Diners Club card numbers begin with 300 through 305, 36 or 38. All have 14 digits.
        // There are Diners Club cards that begin with 5 and have 16 digits.
        // These are a joint venture between Diners Club and MasterCard, and should be processed like a MasterCard
        self::DINERS => [
            '/^3(?:0[0-5]|[68][0-9])[0-9]{11}$/',
        ],
        // Discover card numbers begin with 6011, 622126 through 622925, 644 through 649 or 65.
        // All have 16 digits
        self::DISCOVER => [
            '/^6011[0-9]{12}$/',
            '/^64[4-9][0-9]{13}$/',
            '/^65[0-9]{14}$/',
            '/^622(12[6-9]|1[3-9][0-9]|[2-8][0-9][0-9]|91[0-9]|92[0-5])[0-9]{10}$/',
        ],
        // InstaPayment cards begin with 637 through 639 and have 16 digits
        self::INSTAPAYMENT => [
            '/^63[7-9][0-9]{13}$/',
        ],
        // JCB cards beginning with 2131 or 1800 have 15 digits.
        // JCB cards beginning with 35 have 16 digits
        self::JCB => [
            '/^(?:2131|1800|35[0-9]{3})[0-9]{11}$/',
        ],
        // Laser cards begin with either 6304, 6706, 6709 or 6771 and have between 16 and 19 digits
        self::LASER => [
            '/^(6304|670[69]|6771)[0-9]{12,15}$/',
        ],
        // Maestro international cards begin with 675900..675999 and have between 12 and 19 digits.
        // Maestro UK cards begin with either 500000..509999 or 560000..699999 and have between 12 and 19 digits
        self::MAESTRO => [
            '/^(6759[0-9]{2})[0-9]{6,13}$/',
            '/^(50[0-9]{4})[0-9]{6,13}$/',
            '/^5[6-9][0-9]{10,17}$/',
            '/^6[0-9]{11,18}$/',
        ],
        // All MasterCard numbers start with the numbers 51 through 55. All have 16 digits.
        // October 2016 MasterCard numbers can also start with 222100 through 272099
        self::MASTERCARD => [
            '/^5[1-5][0-9]{14}$/',
            '/^2(22[1-9][0-9]{12}|2[3-9][0-9]{13}|[3-6][0-9]{14}|7[0-1][0-9]{13}|720[0-9]{12})$/',
        ],
        // Payment system MIR numbers start with 220, then 1 digit from 0 to 4, then between 12 and 15 digits
        self::MIR => [
            '/^220[0-4][0-9]{12,15}$/',
        ],
        // All UATP card numbers start with a 1 and have a length of 15 digits
        self::UATP => [
            '/^1[0-9]{14}$/',
        ],
        // All Visa card numbers start with a 4 and have a length of 13, 16, or 19 digits
        self::VISA => [
            '/^4([0-9]{12}|[0-9]{15}|[0-9]{18})$/',
        ],
    ];

    private const UATP = 'UATP';

    private const VISA = 'VISA';

    public function isCreditCardNumberValid(string $number): bool
    {
        // Strip non-numeric characters
        $number = \preg_replace('/\D/', '', $number);

        if (\is_string($number) === false) {
            return false;
        }

        foreach (self::SCHEMES as $regexes) {
            foreach ($regexes as $regex) {
                if (\preg_match($regex, $number) === 1 && $this->isLuhnValid($number)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function isLuhnValid(string $number): bool
    {
        $checkSum = 0;
        $length = \strlen($number);
        $isSecond = false;

        for ($i = $length - 1; $i >= 0; $i--) {
            $current = (int)$number[$i];

            if ($isSecond) {
                $current *= 2;
            }

            $checkSum = $checkSum + \intdiv($current, 10) + $current % 10;
            $isSecond = $isSecond === false;
        }

        return $checkSum !== 0 && $checkSum % 10 === 0;
    }
}
