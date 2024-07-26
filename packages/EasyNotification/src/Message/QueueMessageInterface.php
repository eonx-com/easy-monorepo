<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Message;

interface QueueMessageInterface
{
    public function addHeader(string $name, string $value): self;

    public function getBody(): string;

    /**
     * @return string[]
     */
    public function getHeaders(): array;

    public function getQueueUrl(): string;

    public function setBody(string $body): self;

    public function setQueueUrl(string $queueUrl): self;
}
