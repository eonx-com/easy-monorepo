<?php
declare(strict_types=1);

namespace App\Tests\Quality\Ecs\ArrangeActAssertSniff\Correct;

use Symfony\Contracts\Translation\TranslatorInterface;

final class TestClass
{
    public function testSetTranslatorSucceeds(): void
    {
        $abstractClass = new class() {
            use TranslatorAwareTrait;
        };
        $translator = $this->prophesize(TranslatorInterface::class)->reveal();

        $abstractClass->setTranslator($translator);

        self::assertSame($translator, $this->getPrivatePropertyValue($abstractClass, 'translator'));
    }
}
