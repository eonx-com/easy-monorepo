<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Interfaces;

interface QueueMessageInterface
{
    /**
     * @var string
     */
    public const HEADER_PROVIDER = 'provider';

    /**
     * @var string
     */
    public const HEADER_SIGNATURE = 'signature';

    /**
     * @var string
     */
    public const HEADER_TYPE = 'type';

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
