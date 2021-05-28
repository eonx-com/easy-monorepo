<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony\Messenger\Serializer\Interfaces;

interface MessageObjectFactoryInterface
{
    public function createMessage(QueueEnvelopeInterface $envelope): object;
}
