<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Bridge\Laravel;

use EonX\EasyDecision\Decisions\AffirmativeDecision;
use EonX\EasyDecision\Tests\AbstractLumenTestCase;

final class LaravelDecisionFactoryTest extends AbstractLumenTestCase
{
    public function testSanity(): void
    {
        self::assertInstanceOf(
            AffirmativeDecision::class,
            $this->getDecisionFactory()
                ->createAffirmativeDecision('my-decision')
        );
    }
}
