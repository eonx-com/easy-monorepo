<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Messenger\Stamp;

use Symfony\Component\Messenger\Stamp\NonSendableStampInterface;

final readonly class OriginalMessageStamp implements NonSendableStampInterface
{
    public function __construct(
        private string $body,
        private array $headers,
    ) {
    }

    public static function create(string $body, array $headers): self
    {
        return new self($body, $headers);
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}
