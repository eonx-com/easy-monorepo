<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyPsr7Factory\Tests;

use PHPUnit\Framework\TestCase;

/**
 * This class has for objective to provide common features to all tests without having to update
 * the class they all extend.
 */
abstract class AbstractTestCase extends TestCase
{
    // No body needed.
}

\class_alias(
    AbstractTestCase::class,
    'StepTheFkUp\EasyPsr7Factory\Tests\AbstractTestCase',
    false
);
