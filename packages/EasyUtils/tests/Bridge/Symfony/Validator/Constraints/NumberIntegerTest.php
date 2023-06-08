<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Bridge\Symfony\Validator\Constraints;

use EonX\EasyUtils\Tests\Bridge\Symfony\Fixtures\NumberIntegerDummy;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Mapping\Loader\AnnotationLoader;

class NumberIntegerTest extends TestCase
{
    public function testAttributes(): void
    {
        $metadata = new ClassMetadata(NumberIntegerDummy::class);
        $loader = new AnnotationLoader();
        self::assertTrue($loader->loadClassMetadata($metadata));

        /** @var \EonX\EasyUtils\Bridge\Symfony\Validator\Constraints\NumberInteger $aConstraint */
        [$aConstraint] = $metadata->properties['a']->getConstraints();
        self::assertSame('myMessage', $aConstraint->message);
        self::assertSame(['Default', 'NumberIntegerDummy'], $aConstraint->groups);

        /** @var \EonX\EasyUtils\Bridge\Symfony\Validator\Constraints\NumberInteger $bConstraint */
        [$bConstraint] = $metadata->properties['b']->getConstraints();
        self::assertSame(['my_group'], $bConstraint->groups);
        self::assertSame('some attached data', $bConstraint->payload);
    }
}
