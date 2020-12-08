<?php

$array = [
    'iat' => \time(),
    'iss' => self::TEST_CONST,
    'sub' => $someObject->getValue(),
    SomeFile::TEST_CONST => 'abc',
    self::ANOTHER_TEST_CONST => [
        'fn' => $someObject->getFn(),
        'ln' => $someObject->getLn(),
        'email' => $someObject->getEmail(),
        'phone' => $someObject->getPhone(),
    ],
    'aud' => self::ANOTHER_TEST_CONST,
    'exp' => \time() + 3600,
];

?>
    -----
<?php

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
