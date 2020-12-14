<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Sniffs\Arrays\OrderArrayKeysAlphabeticallySniff\Fixtures\Wrong;

$array = ['iat' => \time(), 'iss' => self::TEST_CONST, 'sub' => $someObject->getValue(), SomeFile::TEST_CONST => 'abc', self::ANOTHER_TEST_CONST => ['fn' => $someObject->getFn(), 'ln' => $someObject->getLn(), 'email' => $someObject->getEmail(), 'phone' => $someObject->getPhone()], 'aud' => self::ANOTHER_TEST_CONST, 'exp' => \time() + 3600];

$array2 = array('iat' => \time(), 'iss' => self::TEST_CONST, 'sub' => $someObject->getValue(), SomeFile::TEST_CONST => 'abc', self::ANOTHER_TEST_CONST => array('fn' => $someObject->getFn(), 'ln' => $someObject->getLn(), 'email' => $someObject->getEmail(), 'phone' => $someObject->getPhone()), 'aud' => self::ANOTHER_TEST_CONST, 'exp' => \time() + 3600);
