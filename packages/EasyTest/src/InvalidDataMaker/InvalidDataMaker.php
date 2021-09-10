<?php

declare(strict_types=1);

namespace EonX\EasyTest\InvalidDataMaker;

use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints\CardScheme;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Country;
use Symfony\Component\Validator\Constraints\Currency;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\Luhn;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotEqualTo;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Timezone;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Constraints\Uuid;

/**
 * @codeCoverageIgnore
 */
class InvalidDataMaker extends AbstractInvalidDataMaker
{
    /**
     * @return iterable<mixed>
     */
    public function yieldArrayCollectionWithFewerItems(int $minElements): iterable
    {
        $value = new ArrayCollection(\array_fill(0, $minElements - 1, null));
        $message = $this->translateMessage(
            (new Count(['min' => $minElements]))->minMessage,
            [
                '{{ limit }}' => $minElements,
            ],
            $minElements
        );

        yield from $this->create("{$this->property} has too few elements in the collection", $value, $message);
    }

    /**
     * @return iterable<mixed>
     */
    public function yieldArrayCollectionWithMoreItems(int $maxElements): iterable
    {
        $value = new ArrayCollection(\array_fill(0, $maxElements - 1, null));
        $message = $this->translateMessage(
            (new Count(['max' => $maxElements]))->maxMessage,
            [
                '{{ limit }}' => $maxElements,
            ],
            $maxElements
        );

        yield from $this->create("{$this->property} has too many elements in the collection", $value, $message);
    }

    /**
     * @return iterable<mixed>
     */
    public function yieldArrayWithFewerItems(int $minElements): iterable
    {
        $value = \array_fill(0, $minElements - 1, null);
        $message = $this->translateMessage(
            (new Count(['min' => $minElements]))->minMessage,
            [
                '{{ limit }}' => $minElements,
            ],
            $minElements
        );

        yield from $this->create("{$this->property} has too few elements in the array", $value, $message);
    }

    /**
     * @param mixed|null $itemValue
     *
     * @return iterable<mixed>
     */
    public function yieldArrayWithMoreItems(int $maxElements, $itemValue = null): iterable
    {
        $value = \array_fill(0, $maxElements + 1, $itemValue);
        $message = $this->translateMessage(
            (new Count(['max' => $maxElements]))->maxMessage,
            [
                '{{ limit }}' => $maxElements,
            ],
            $maxElements
        );

        yield from $this->create("{$this->property} has too many elements in the array", $value, $message);
    }

    /**
     * @return iterable<mixed>
     */
    public function yieldBlankString(): iterable
    {
        $value = '';
        $message = $this->translateMessage((new NotBlank())->message);

        yield from $this->create("{$this->property} is blank", $value, $message);
    }

    /**
     * @return iterable<mixed>
     */
    public function yieldDateTimeLessThanOrEqualRelatedProperty(string $relatedProperty): iterable
    {
        $dateTime = Carbon::now();
        $value = $dateTime->clone()
            ->subSecond()
            ->toAtomString();
        $this->relatedPropertyValue = $dateTime->toAtomString();
        $this->relatedProperty = $relatedProperty;

        $message = $this->translateMessage(
            (new GreaterThanOrEqual(['value' => 'now']))->message,
            [
                '{{ compared_value }}' => \sprintf('"%s"', $this->relatedPropertyValue),
            ]
        );

        yield from $this->create("{$this->property} has less datetime than {$this->relatedProperty}", $value, $message);
    }

    /**
     * @return iterable<mixed>
     */
    public function yieldDateTimeLessThanOrEqualToNow(): iterable
    {
        $dateTime = Carbon::now();
        $message = $this->translateMessage(
            (new GreaterThan(['value' => 'now']))->message,
            [
                '{{ compared_value }}' => 'now',
            ]
        );

        $value = $dateTime->clone()
            ->subSecond()
            ->toAtomString();

        yield from $this->create("{$this->property} has less datetime", $value, $message);

        $value = $dateTime->toAtomString();

        yield from $this->create("{$this->property} has equal datetime", $value, $message);
    }

    /**
     * @return iterable<mixed>
     */
    public function yieldDateTimeLessThanRelatedProperty(string $relatedProperty): iterable
    {
        $dateTime = Carbon::now();
        $value = $dateTime->clone()
            ->subSecond()
            ->toAtomString();
        $this->relatedPropertyValue = $dateTime->toAtomString();
        $this->relatedProperty = $relatedProperty;

        $message = $this->translateMessage(
            (new GreaterThan(['value' => 'now']))->message,
            [
                '{{ compared_value }}' => \sprintf('"%s"', $this->relatedPropertyValue),
            ]
        );

        yield from $this->create("{$this->property} has less datetime than {$this->relatedProperty}", $value, $message);
    }

    /**
     * @return iterable<mixed>
     */
    public function yieldEmptyArray(): iterable
    {
        yield from $this->yieldArrayWithFewerItems(1);
    }

    /**
     * @return iterable<mixed>
     */
    public function yieldEmptyArrayCollection(): iterable
    {
        yield from $this->yieldArrayCollectionWithFewerItems(1);
    }

    /**
     * @return iterable<mixed>
     */
    public function yieldIntegerGreaterThanGiven(int $lessThanOrEqualValue): iterable
    {
        $value = $lessThanOrEqualValue + 1;
        $message = $this->translateMessage(
            (new LessThanOrEqual(['value' => $value]))->message,
            [
                '{{ compared_value }}' => $lessThanOrEqualValue,
            ]
        );

        yield from $this->create("{$this->property} has greater value", $value, $message);
    }

    /**
     * @return iterable<mixed>
     */
    public function yieldIntegerGreaterThanOrEqualToGiven(int $lessThanValue): iterable
    {
        $value = $lessThanValue + 1;
        $message = $this->translateMessage(
            (new LessThan(['value' => $value]))->message,
            [
                '{{ compared_value }}' => $lessThanValue,
            ]
        );

        yield from $this->create("{$this->property} has greater value", $value, $message);

        $value = $lessThanValue;
        $message = $this->translateMessage(
            (new LessThan(['value' => $value]))->message,
            [
                '{{ compared_value }}' => $lessThanValue,
            ]
        );

        yield from $this->create("{$this->property} has equal value", $value, $message);
    }

    /**
     * @return iterable<mixed>
     */
    public function yieldInvalidChoice(): iterable
    {
        $value = 'invalid-choice';
        $message = $this->translateMessage((new Choice())->message);

        yield from $this->create("{$this->property} is not a valid choice", $value, $message);
    }

    /**
     * @return iterable<mixed>
     */
    public function yieldInvalidCountry(): iterable
    {
        $value = 'invalid-country';
        $message = $this->translateMessage((new Country())->message);

        yield from $this->create("{$this->property} is invalid country", $value, $message);
    }

    /**
     * @return iterable<mixed>
     */
    public function yieldInvalidCreditCardNumber(): iterable
    {
        $value = '1111222233334444';
        $message = $this->translateMessage((new CardScheme(['schemes' => null]))->message);

        yield from $this->create("{$this->property} is not a valid credit card number", $value, $message);
    }

    /**
     * @return iterable<mixed>
     */
    public function yieldInvalidCurrencyCode(): iterable
    {
        $value = 'invalid-currency-code';
        $message = $this->translateMessage((new Currency())->message);

        yield from $this->create("{$this->property} is invalid currency", $value, $message);
    }

    /**
     * @return iterable<mixed>
     */
    public function yieldInvalidDateTime(): iterable
    {
        $value = 'invalid-datetime';
        $message = $this->translateMessage((new DateTime())->message);

        yield from $this->create("{$this->property} is invalid datetime", $value, $message);
    }

    /**
     * @return iterable<mixed>
     */
    public function yieldInvalidEmail(): iterable
    {
        $value = 'invalid-email';
        $message = $this->translateMessage((new Email())->message);

        yield from $this->create("{$this->property} is invalid email", $value, $message);
    }

    /**
     * @return iterable<mixed>
     */
    public function yieldInvalidExactLengthString(int $exactLength): iterable
    {
        $message = $this->translateMessage(
            (new Length([
                'max' => $exactLength,
                'min' => $exactLength,
            ]))->exactMessage,
            ['{{ limit }}' => $exactLength],
            $exactLength
        );

        $value = \str_pad('1', $exactLength + 1, '1');

        yield from $this->create("{$this->property} has length more than expected", $value, $message);

        $value = \str_pad('', $exactLength - 1, '1');

        yield from $this->create("{$this->property} has length less than expected", $value, $message);
    }

    /**
     * @return iterable<mixed>
     */
    public function yieldInvalidTimezone(): iterable
    {
        $value = 'invalid-timezone';
        $message = $this->translateMessage((new Timezone())->message);

        yield from $this->create("{$this->property} is invalid timezone", $value, $message);
    }

    /**
     * @return iterable<mixed>
     */
    public function yieldInvalidUrl(): iterable
    {
        $value = 'some invalid url';
        $message = $this->translateMessage((new Url())->message);

        yield from $this->create("{$this->property} is invalid url", $value, $message);
    }

    /**
     * @return iterable<mixed>
     */
    public function yieldInvalidUuid(): iterable
    {
        $value = 'some-invalid-uuid';
        $message = $this->translateMessage((new Uuid())->message);

        yield from $this->create("{$this->property} is invalid uuid", $value, $message);
    }

    /**
     * @return iterable<mixed>
     */
    public function yieldNegativeNumber(): iterable
    {
        $value = -1;
        $message = $this->translateMessage((new PositiveOrZero())->message);

        yield from $this->create("{$this->property} has negative value", $value, $message);
    }

    /**
     * @return iterable<mixed>
     */
    public function yieldNegativeOrZeroNumber(): iterable
    {
        $message = $this->translateMessage((new Positive())->message);

        $value = -1;

        yield from $this->create("{$this->property} has negative value", $value, $message);

        $value = 0;

        yield from $this->create("{$this->property} has zero value", $value, $message);
    }

    /**
     * @return iterable<mixed>
     */
    public function yieldNonDigitSymbols(): iterable
    {
        $value = '111-aaa';
        $message = $this->translateMessage(
            (new Type(['type' => 'digit']))->message,
            [
                '{{ type }}' => 'digit',
            ]
        );

        yield from $this->create("{$this->property} has non-digit symbols", $value, $message);
    }

    /**
     * @return iterable<mixed>
     */
    public function yieldNonLuhnCreditCardNumber(): iterable
    {
        $value = '4388576018402626';
        $message = $this->translateMessage((new Luhn())->message);

        yield from $this->create("{$this->property} do not pass the Luhn algorithm", $value, $message);
    }

    /**
     * @return iterable<mixed>
     */
    public function yieldNotEqualTo(string $value): iterable
    {
        $message = $this->translateMessage(
            (new NotEqualTo())->message,
            [
                '{{ compared_value }}' => $value,
            ]
        );

        yield from $this->create(
            "{$this->property} is not equal to {$value}",
            'not-equal-to' . $value,
            $message
        );
    }

    /**
     * @return iterable<mixed>
     */
    public function yieldOutOfRangeNumber(int $min, int $max): iterable
    {
        $message = $this->translateMessage(
            (new Range(\compact('min', 'max')))->notInRangeMessage,
            [
                '{{ min }}' => $min,
                '{{ max }}' => $max,
            ]
        );

        $value = $max + 1;

        yield from $this->create("{$this->property} is out of range (above)", $value, $message);

        $value = $min - 1;

        yield from $this->create("{$this->property} is out of range (below)", $value, $message);
    }

    /**
     * @return iterable<mixed>
     */
    public function yieldTooLongString(int $maxLength): iterable
    {
        $value = \str_pad('g', $maxLength + 1, 'g');
        $message = $this->translateMessage(
            (new Length(['max' => $maxLength]))->maxMessage,
            [
                '{{ limit }}' => $maxLength,
            ],
            $maxLength
        );

        yield from $this->create("{$this->property} is too long", $value, $message);
    }

    /**
     * @return iterable<mixed>
     */
    public function yieldTooShortString(int $minLength): iterable
    {
        $value = $minLength > 1 ? \str_pad('g', $minLength - 1, 'g') : '';
        $message = $this->translateMessage(
            (new Length(['min' => $minLength]))->minMessage,
            [
                '{{ limit }}' => $minLength,
            ],
            $minLength
        );

        yield from $this->create("{$this->property} is too short", $value, $message);
    }
}
