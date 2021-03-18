<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Sniffs\Arrays\AlphabeticallySortedArrayKeysSniff\Fixtures\Correct;

$array = [
    'aud' => self::ANOTHER_TEST_CONST,
    'exp' => \time() + 3600,
    'iat' => \time(),
    'iss' => self::TEST_CONST,
    self::ANOTHER_TEST_CONST => [
        'email' => $someObject->getEmail(),
        'fn' => $someObject->getFn(),
        'ln' => $someObject->getLn(),
        'phone' => $someObject->getPhone(),
    ],
    SomeFile::TEST_CONST => 'abc',
    'sub' => $someObject->getValue(),
];

$array2 = array(
    'aud' => self::ANOTHER_TEST_CONST,
    'exp' => \time() + 3600,
    'iat' => \time(),
    'iss' => self::TEST_CONST,
    self::ANOTHER_TEST_CONST => array(
        'email' => $someObject->getEmail(),
        'fn' => $someObject->getFn(),
        'ln' => $someObject->getLn(),
        'phone' => $someObject->getPhone(),
    ),
    SomeFile::TEST_CONST => 'abc',
    'sub' => $someObject->getValue(),
);

$array3 = [
    'description' => $someObject->getStatementDescriptor(),
    'matures_at' => Carbon::now()->toIso8601ZuluString(),
    'payouts' => [
        [
            'amount' => $someObject->getFinalAmount(),
            'description' => $someObject->getStatementDescriptor(),
            'metadata' => [
                'eonx_transaction_id' => $someObject->getId(),
            ],
            'recipient_contact_id' => $someObject->getSplitContactOrFail()->getContactId(),
        ],
    ],
];
