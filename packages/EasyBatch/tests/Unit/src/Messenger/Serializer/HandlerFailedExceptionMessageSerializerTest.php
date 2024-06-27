<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Unit\Messenger\Serializer;

use EonX\EasyBatch\Bundle\Enum\ConfigServiceId;
use EonX\EasyBatch\Messenger\Serializer\HandlerFailedExceptionMessageSerializer;
use EonX\EasyBatch\Tests\Unit\AbstractSymfonyTestCase;

final class HandlerFailedExceptionMessageSerializerTest extends AbstractSymfonyTestCase
{
    public function testMessageSerializerDecoratedSucceeds(): void
    {
        /** @var \EonX\EasyBatch\Common\Serializer\MessageSerializerInterface $messageSerializer */
        $messageSerializer = $this->getKernel()
            ->getContainer()
            ->get(ConfigServiceId::BatchMessageSerializer->value);

        self::assertSame(HandlerFailedExceptionMessageSerializer::class, $messageSerializer::class);
    }
}
