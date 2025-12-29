<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Unit\Common\Translator;

use EonX\EasyUtils\Common\Translator\TranslatorAwareTrait;
use EonX\EasyUtils\Tests\Unit\AbstractUnitTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

final class TranslatorAwareTraitTest extends AbstractUnitTestCase
{
    public function testSetTranslatorSucceeds(): void
    {
        $abstractClass = new class() {
            use TranslatorAwareTrait;
        };
        $translator = $this->mock(TranslatorInterface::class);

        $abstractClass->setTranslator($translator);

        self::assertSame($translator, self::getPrivatePropertyValue($abstractClass, 'translator'));
    }
}
