<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Unit\Bundle;

use EonX\EasyDecision\Decision\AffirmativeDecision;

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
