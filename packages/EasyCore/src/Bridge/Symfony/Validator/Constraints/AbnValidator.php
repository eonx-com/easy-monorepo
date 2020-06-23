<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\Validator\Constraints;

use EoneoPay\Utils\Exceptions\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * Validates an Australian Business Number.
 *
 * @link https://abr.business.gov.au/Help/AbnFormat
 */
final class AbnValidator extends ConstraintValidator
{
    /**
     * @var int
     */
    private const ABN_LENGTH = 11;

    /**
     * @var int
     */
    private const MODULUS = 89;

    /**
     * @var int[]
     */
    private const WEIGHTS = [10, 1, 3, 5, 7, 9, 11, 13, 15, 17, 19];

    /**
     * Validates an Australian Business Number.
     *
     * @throws \EoneoPay\Utils\Exceptions\UnexpectedTypeException
     */
    public function validate($value, Constraint $constraint): void
    {
        if ($constraint instanceof Abn === false) {
            throw new UnexpectedTypeException($constraint, Abn::class);
        }

        if ($value === null || $value === '') {
            return;
        }

        if (\is_string($value) === false && (\is_object($value) && method_exists($value, '__toString')) === false) {
            throw new UnexpectedValueException($value, 'string');
        }

        $abn = (string) $value;

        if (\strlen($abn) !== self::ABN_LENGTH) {
            $this->context->buildViolation($constraint->message)
                ->setCode(Abn::INVALID_LENGTH_ERROR)
                ->addViolation();

            return;
        }

        if (\ctype_digit($abn) === false || (int)$abn[0] === 0) {
            $this->context->buildViolation($constraint->message)
                ->setCode(Abn::INVALID_CHARACTERS_ERROR)
                ->addViolation();

            return;
        }

        $abn[0] = (int)$abn[0] - 1;
        $sum = 0;
        foreach (\str_split($abn) as $key => $digit) {
            $sum += (int)$digit * self::WEIGHTS[$key];
        }

        if ($sum % self::MODULUS !== 0) {
            $this->context->buildViolation($constraint->message)
                ->setCode(Abn::MODULUS_CALCULATION_FAILED_ERROR)
                ->addViolation();
        }
    }
}
