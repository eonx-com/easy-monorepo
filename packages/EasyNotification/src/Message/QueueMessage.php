<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Message;

use BackedEnum;
use EonX\EasyNotification\Enum\Header;
use SplObjectStorage;

final class QueueMessage implements QueueMessageInterface
{
    private string $body;

    /**
     * @var \SplObjectStorage<\EonX\EasyNotification\Enum\Header, string>
     */
    private SplObjectStorage $headers;

    private string $queueUrl;

    public function __construct()
    {
        $this->headers = new SplObjectStorage();
    }

    public function addHeader(Header $header, string|BackedEnum $value): QueueMessageInterface
    {
        $this->headers->offsetSet($header, \is_string($value) ? $value : (string)$value->value);

        return $this;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getHeaders(): SplObjectStorage
    {
        return $this->headers;
    }

    public function getQueueUrl(): string
    {
        return $this->queueUrl;
    }

    public function setBody(string $body): QueueMessageInterface
    {
        $this->body = $body;

        return $this;
    }

    public function setQueueUrl(string $queueUrl): QueueMessageInterface
    {
        $this->queueUrl = $queueUrl;

        return $this;
    }
}
