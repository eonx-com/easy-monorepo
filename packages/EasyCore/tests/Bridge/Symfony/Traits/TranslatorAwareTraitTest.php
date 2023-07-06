<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\Traits;

use EonX\EasyCore\Bridge\Symfony\Traits\TranslatorAwareTrait;
use EonX\EasyCore\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

final class TranslatorAwareTraitTest extends AbstractSymfonyTestCase
{
    public function testSetTranslatorSucceeds(): void
    {
        $abstractClass = new class() {
            use TranslatorAwareTrait;
        };
        /** @var \Symfony\Contracts\Translation\TranslatorInterface $translator */
        $translator = self::mock(TranslatorInterface::class);

        $abstractClass->setTranslator($translator);

        self::assertSame($translator, $this->getPrivatePropertyValue($abstractClass, 'translator'));
    }
}
