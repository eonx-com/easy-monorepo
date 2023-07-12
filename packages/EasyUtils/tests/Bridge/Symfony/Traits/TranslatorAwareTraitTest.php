<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Bridge\Symfony\Traits;

use EonX\EasyUtils\Bridge\Symfony\Traits\TranslatorAwareTrait;
use EonX\EasyUtils\Tests\AbstractTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

final class TranslatorAwareTraitTest extends AbstractTestCase
{
    public function testSetTranslatorSucceeds(): void
    {
        $abstractClass = new class() {
            use TranslatorAwareTrait;
        };
        /** @var \Symfony\Contracts\Translation\TranslatorInterface $translator */
        $translator = $this->mock(TranslatorInterface::class);

        $abstractClass->setTranslator($translator);

        self::assertSame($translator, $this->getPrivatePropertyValue($abstractClass, 'translator'));
    }
}
