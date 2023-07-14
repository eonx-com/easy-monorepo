<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Messages;

use EonX\EasyNotification\Interfaces\MessageInterface;
use Nette\Utils\Json;

abstract class AbstractMessage implements MessageInterface
{
    /**
     * @param null|mixed[] $body
     */
    public function __construct(
        private ?array $body = null,
    ) {
    }

    /**
     * @param mixed[] $body
     */
    public function body(array $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getBody(): string
    {
        return Json::encode($this->body);
    }

    /**
     * @param mixed[] $body
     */
    public function mergeBody(array $body): self
    {
        $this->body = \array_merge($this->body ?? [], $body);

        return $this;
    }
}
