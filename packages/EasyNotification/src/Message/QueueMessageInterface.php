<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Message;

use BackedEnum;
use EonX\EasyNotification\Enum\Header;
use SplObjectStorage;

interface QueueMessageInterface
{
    public function addHeader(Header $header, string|BackedEnum $value): self;

    public function getBody(): string;

    /**
     * @return \SplObjectStorage<\EonX\EasyNotification\Enum\Header, string>
     */
    public function getHeaders(): SplObjectStorage;

    public function getQueueUrl(): string;

    public function setBody(string $body): self;

    public function setQueueUrl(string $queueUrl): self;
}
