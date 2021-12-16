<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Bridge\Symfony\Serializers;

use EonX\EasyBatch\Bridge\BridgeConstantsInterface;
use EonX\EasyBatch\Bridge\Symfony\Serializers\MessageSerializerDecorator;
use EonX\EasyBatch\Tests\Bridge\Symfony\AbstractSymfonyTestCase;

final class MessageSerializerDecoratorTest extends AbstractSymfonyTestCase
{
    public function testSerializeSucceeds(): void
    {
        /** @var \EonX\EasyBatch\Interfaces\MessageSerializerInterface $serializer */
        $serializer = $this->getKernel()
            ->getContainer()
            ->get(BridgeConstantsInterface::SERVICE_BATCH_SERIALIZER);

        self::assertSame(MessageSerializerDecorator::class, get_class($serializer));
    }
}
