<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony\Messenger\Serializer;

use Symfony\Component\Messenger\Stamp\NonSendableStampInterface;

final class OriginalMessageStamp implements NonSendableStampInterface
{
    /**
     * @var string
     */
    private $body;

    /**
     * @var mixed[]
     */
    private $headers;

    /**
     * @param mixed[] $headers
     */
    public function __construct(string $body, array $headers)
    {
        $this->body = $body;
        $this->headers = $headers;
    }

    /**
     * @param mixed[] $headers
     */
    public static function create(string $body, array $headers): OriginalMessageStamp
    {
        return new self($body, $headers);
    }

    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @return mixed[]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
