<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Bridge\Symfony\Serializer;

use EonX\EasyBatch\Bridge\BridgeConstantsInterface;
use EonX\EasyBatch\Bridge\Symfony\Serializer\SerializerDecorator;
use EonX\EasyBatch\Tests\Bridge\Symfony\AbstractSymfonyTestCase;

class SerializerDecoratorTest extends AbstractSymfonyTestCase
{
    public function testSerializeSucceeds(): void
    {
        /** @var \EonX\EasyBatch\Interfaces\SerializerInterface $serializer */
        $serializer = $this->getKernel()
            ->getContainer()
            ->get(BridgeConstantsInterface::SERVICE_BATCH_SERIALIZER);

        self::assertSame(SerializerDecorator::class, get_class($serializer));
    }
}
