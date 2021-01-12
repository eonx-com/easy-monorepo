<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\Traits;

use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;

trait InvalidDataGeneratorTrait
{
    /**
     * @param mixed[] $cases
     *
     * @return mixed[]
     */
    public function asString(array $cases): array
    {
        return \array_map(static function (array $case): array {
            $case['data'] = \array_map(static function ($value) {
                return (string)$value;
            }, $case['data']);

            return $case;
        }, $cases);
    }

    /**
     * @return mixed[]
     */
    public function getArrayCollectionWithFewerItems(string $property, int $minElements): array
    {
        $fewerElements = \array_fill(0, $minElements - 1, null);
        $fewerArrayCollection = new ArrayCollection($fewerElements);

        return [
            "$property has too few elements in the collection" => [
                'data' => [
                    $property => $fewerArrayCollection,
                ],
                'message' => "This collection should contain $minElements element or more.",
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public function getArrayWithFewerItems(string $property, int $minElements): array
    {
        return [
            "$property has too few elements in the array" => [
                'data' => [
                    $property => \array_fill(0, $minElements - 1, null),
                ],
                'message' => "This collection should contain $minElements element or more.",
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public function getArrayWithMoreItems(string $property, int $maxElements): array
    {
        return [
            "$property has too many elements in the collection" => [
                'data' => [
                    $property => \array_fill(0, $maxElements + 1, []),
                ],
                'message' => "This collection should contain $maxElements elements or less.",
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public function getBlankString(string $property): array
    {
        return [
            "$property is blank" => [
                'data' => [
                    $property => '',
                ],
                'message' => 'This value should not be blank.',
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public function getBlankStringInArray(string $property): array
    {
        return [
            "$property is blank" => [
                'data' => [
                    $property => [''],
                ],
                'message' => 'This value should not be blank.',
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public function getDateTimeLessThanOrEqualToNow(string $property): array
    {
        $dateTime = Carbon::now();

        return [
            "$property has less datetime" => [
                'data' => [
                    $property => $dateTime->clone()->subSecond()->toAtomString(),
                ],
                'message' => 'This value should be greater than now.',
            ],
            "$property has equal datetime" => [
                'data' => [
                    $property => $dateTime->toAtomString(),
                ],
                'message' => 'This value should be greater than now.',
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public function getEmptyArrayCollection(string $property, int $minElements): array
    {
        return [
            "$property has too few elements in the collection" => [
                'data' => [
                    $property => new ArrayCollection(),
                ],
                'message' => "This collection should contain $minElements element or more.",
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public function getIntegerGreaterThanGiven(string $property, int $lessThanOrEqualValue): array
    {
        return [
            "$property has greater value" => [
                'data' => [
                    $property => $lessThanOrEqualValue + 1,
                ],
                'message' => "This value should be less than or equal to $lessThanOrEqualValue.",
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public function getIntegerGreaterThanOrEqualToGiven(string $property, int $lessThanValue): array
    {
        return [
            "$property has greater value" => [
                'data' => [
                    $property => $lessThanValue + 1,
                ],
                'message' => "This value should be less than $lessThanValue.",
            ],
            "$property has equal value" => [
                'data' => [
                    $property => $lessThanValue,
                ],
                'message' => "This value should be less than $lessThanValue.",
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public function getInvalidCurrency(string $property): array
    {
        return [
            "$property is invalid currency" => [
                'data' => [
                    $property => 'invalid-currency',
                ],
                'message' => 'This value is not a valid currency.',
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public function getInvalidEmail(string $property): array
    {
        return [
            "$property is invalid email" => [
                'data' => [
                    $property => 'invalid-email',
                ],
                'message' => 'This value is not a valid email address.',
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public function getInvalidExactLengthString(string $property, int $exactLength): array
    {
        return [
            "$property has invalid length" => [
                'data' => [
                    $property => \str_pad('12345678909', $exactLength + 1, '1'),
                ],
                'message' => "This value should have exactly $exactLength characters.",
            ],
        ];
    }

    /**
     * @param mixed[] $invalidData
     *
     * @noinspection MultipleReturnStatementsInspection
     *
     * @todo this indirect resolving of violation path is probably need to be rethinked
     */
    public function getInvalidPropertyName(array $invalidData): string
    {
        $propertyName = (string)\array_key_first($invalidData);

        if (\is_array($invalidData[$propertyName]) && \count($invalidData[$propertyName]) > 0) {
            // The case of stubs collection ('prop' => [ [], [], [], [] ])
            if (($invalidData[$propertyName][0] ?? null) === []) {
                return $propertyName;
            }

            $currentProperty = \current(\array_keys($invalidData[$propertyName]));

            if ($currentProperty === 0) {
                return $propertyName . '[0]';
            }

            return $propertyName . '.' . $this->getInvalidPropertyName($invalidData[$propertyName]);
        }

        return $propertyName;
    }

    /**
     * @return mixed[]
     */
    public function getInvalidTimezone(string $property): array
    {
        return [
            "${property} is invalid timezone" => [
                'data' => [
                    $property => 'invalid-timezone',
                ],
                'message' => 'This value is not a valid timezone.',
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public function getInvalidUrl(string $property): array
    {
        return [
            "${property} is invalid url" => [
                'data' => [
                    $property => 'invalid-url',
                ],
                'message' => 'This value is not a valid URL.',
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public function getInvalidUuid(string $property): array
    {
        return [
            "${property} is invalid uuid" => [
                'data' => [
                    $property => 'some-invalid-uuid',
                ],
                'message' => 'This is not a valid UUID.',
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public function getNegativeNumber(string $property): array
    {
        return [
            "${property} has negative value" => [
                'data' => [
                    $property => -1,
                ],
                'message' => 'This value should be either positive or zero.',
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public function getNegativeOrZeroNumber(string $property): array
    {
        return [
            "${property} has negative value" => [
                'data' => [
                    $property => -1,
                ],
                'message' => 'This value should be positive.',
            ],
            "${property} has zero value" => [
                'data' => [
                    $property => 0,
                ],
                'message' => 'This value should be positive.',
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public function getNonDigitSymbols(string $property): array
    {
        return [
            "${property} has non-digit symbols" => [
                'data' => [
                    $property => '111-aaa',
                ],
                'message' => 'This value should be of type digit.',
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public function getNonLuhnCreditCardNumber(string $property): array
    {
        return [
            "${property} do not pass the Luhn algorithm" => [
                'data' => [
                    $property => '4388576018402626',
                ],
                'message' => 'Invalid card number.',
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public function getNotValidChoice(string $property): array
    {
        return [
            "${property} is not a valid choice" => [
                'data' => [
                    $property => 'invalid-type',
                ],
                'message' => 'The value you selected is not a valid choice.',
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public function getNotValidChoiceInArray(string $property): array
    {
        return [
            "${property} is not a valid choice in array" => [
                'data' => [
                    $property => ['invalid-type'],
                ],
                'message' => 'The value you selected is not a valid choice.',
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public function getNotValidCreditCardNumber(string $property): array
    {
        return [
            "${property} is not a valid credit card number" => [
                'data' => [
                    $property => '1111222233334444',
                ],
                'message' => 'Unsupported card type or invalid card number.',
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public function getNotValidDateInterval(string $property): array
    {
        return [
            "${property} is not a valid date interval" => [
                'data' => [
                    $property => 'invalid-date-interval',
                ],
                'message' => 'This value is not a valid DateInterval.',
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public function getNotValidFloat(string $property, int $precision, ?int $integerPart = null): array
    {
        return [
            "${property} is not a valid float" => [
                'data' => [
                    $property => ($integerPart ?? 0) + \round(1 / 3, $precision + 1),
                ],
                'message' => \sprintf(
                    'This value is not a valid decimal number or has more than %s digits in a precision.',
                    $precision
                ),
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public function getOutOfRangeNumber(string $property, int $min, int $max): array
    {
        return [
            "${property} is out of range (above)" => [
                'data' => [
                    $property => $max + 1,
                ],
                'message' => "This value should be between {$min} and {$max}.",
            ],
            "${property} is out of range (below)" => [
                'data' => [
                    $property => $min - 1,
                ],
                'message' => "This value should be between {$min} and {$max}.",
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public function getTooLongString(string $property, int $maxLength): array
    {
        return [
            "${property} is too long" => [
                'data' => [
                    $property => \str_pad('g', $maxLength + 1, 'g'),
                ],
                'message' => "This value is too long. It should have $maxLength characters or less.",
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public function getTooLongStringInArray(string $property, int $maxLength): array
    {
        return [
            "${property} is too long" => [
                'data' => [
                    $property => [\str_pad('g', $maxLength + 1, 'g')],
                ],
                'message' => "This value is too long. It should have $maxLength characters or less.",
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public function getTooShortString(string $property, int $minLength): array
    {
        return [
            "${property} is too short" => [
                'data' => [
                    $property => $minLength > 1 ? \str_pad('g', $minLength - 1, 'g') : '',
                ],
                'message' => "This value is too short. It should have $minLength characters or more.",
            ],
        ];
    }

    /**
     * @param mixed[] $cases
     *
     * @return mixed[]
     */
    public function wrapWith(string $property, array $cases): array
    {
        $result = [];

        foreach ($cases as $caseName => $caseValue) {
            $data = $caseValue['data'];
            $innerProperty = \current(\array_keys($data));
            /** @var string $newCaseName */
            $newCaseName = \str_replace($innerProperty, "${property}.${innerProperty}", $caseName);
            $result[$newCaseName] = [
                'data' => [$property => $data],
                'message' => $caseValue['message'],
            ];
        }

        return $result;
    }
}
