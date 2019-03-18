<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Tests;

use PHPUnit\Framework\TestCase;
use StepTheFkUp\EasyDecision\Interfaces\RuleInterface;
use StepTheFkUp\EasyDecision\Tests\Stubs\RuleStub;

/**
 * This class has for objective to provide common features to all tests without having to update
 * the class they all extend.
 */
abstract class AbstractTestCase extends TestCase
{
    /**
     * Create rule which returns false.
     *
     * @param string $name
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\RuleInterface
     */
    protected function createFalseRule(string $name): RuleInterface
    {
        return new RuleStub($name, false);
    }

    /**
     * Create rule which returns true.
     *
     * @param string $name
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\RuleInterface
     */
    protected function createTrueRule(string $name): RuleInterface
    {
        return new RuleStub($name, true);
    }

    /**
     * Create rule which will be unsupported.
     *
     * @param string $name
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\RuleInterface
     */
    protected function createUnsupportedRule(string $name): RuleInterface
    {
        return new RuleStub($name, null, false);
    }
}