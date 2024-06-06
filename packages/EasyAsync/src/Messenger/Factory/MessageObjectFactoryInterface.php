<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Messenger\Factory;

use EonX\EasyAsync\Messenger\Envelope\QueueEnvelopeInterface;

interface MessageObjectFactoryInterface
{
    public function createMessage(QueueEnvelopeInterface $envelope): object;
}
