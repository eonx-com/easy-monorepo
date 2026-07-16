<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Common\Validator;

use EonX\EasyUtils\Common\Constraint\Acn;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * Validates an Australian Company Number.
 *
 * @link https://asic.gov.au/for-business/registering-a-company/steps-to-register-a-company/australian-company-numbers/australian-company-number-digit-check/
 */
final class AcnValidator extends ConstraintValidator
{
    private const int ACN_LENGTH = 9;

    private const int MODULUS = 10;

    private const array WEIGHTS = [8, 7, 6, 5, 4, 3, 2, 1, 0];

    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($constraint instanceof Acn === false) {
            throw new UnexpectedTypeException($constraint, Acn::class);
        }

        if ($value === null || $value === '') {
            return;
        }

        if (\is_string($value) === false && (\is_object($value) && \method_exists($value, '__toString')) === false) {
            throw new UnexpectedValueException($value, 'string');
        }

        $acn = (string)$value;

        $violationCode = $this->getViolationCode($acn);
        if ($violationCode !== null) {
            $this->context->buildViolation($constraint->message)
                ->setCode($violationCode)
                ->addViolation();

            return;
        }

        $sum = 0;
        foreach (\str_split($acn) as $key => $digit) {
            $sum += (int)$digit * self::WEIGHTS[$key];
        }

        $remainder = $sum % self::MODULUS;
        $complement = (string)(self::MODULUS - $remainder);

        if ($complement === '10') {
            $complement = '0';
        }

        if ($acn[8] !== $complement || $acn === '000000000') {
            $this->context->buildViolation($constraint->message)
                ->setCode(Acn::COMPLEMENT_CALCULATION_FAILED_ERROR)
                ->addViolation();
        }
    }

    private function getViolationCode(string $acn): ?string
    {
        if (\strlen($acn) !== self::ACN_LENGTH) {
            return Acn::INVALID_LENGTH_ERROR;
        }

        if (\ctype_digit($acn) === false) {
            return Acn::INVALID_CHARACTERS_ERROR;
        }

        return null;
    }
}
