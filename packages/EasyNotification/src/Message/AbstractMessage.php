<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Message;

use Nette\Utils\Json;

abstract class AbstractMessage implements MessageInterface
{
    public function __construct(
        private ?array $body = null,
    ) {
    }

    public function body(array $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getBody(): string
    {
        return Json::encode($this->body);
    }

    public function mergeBody(array $body): self
    {
        $this->body = \array_merge($this->body ?? [], $body);

        return $this;
    }
}
