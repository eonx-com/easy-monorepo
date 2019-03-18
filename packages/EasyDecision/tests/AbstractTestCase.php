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
     * @param null|int $priority
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\RuleInterface
     */
    protected function createFalseRule(string $name, ?int $priority = null): RuleInterface
    {
        return new RuleStub($name, false, null, $priority);
    }

    /**
     * Create rule which returns true.
     *
     * @param string $name
     * @param null|int $priority
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\RuleInterface
     */
    protected function createTrueRule(string $name, ?int $priority = null): RuleInterface
    {
        return new RuleStub($name, true, null, $priority);
    }

    /**
     * Create rule which will be unsupported.
     *
     * @param string $name
     * @param null|int $priority
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\RuleInterface
     */
    protected function createUnsupportedRule(string $name, ?int $priority = null): RuleInterface
    {
        return new RuleStub($name, null, false, $priority);
    }
}