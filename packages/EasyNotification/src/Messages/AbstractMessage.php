<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Messages;

use EonX\EasyNotification\Interfaces\MessageInterface;
use Nette\Utils\Json;

abstract class AbstractMessage implements MessageInterface
{
    /**
     * @var null|mixed[]
     */
    private $body;

    /**
     * @param null|string[] $body
     */
    public function __construct(?array $body = null)
    {
        $this->body = $body;
    }

    /**
     * @param mixed[] $body
     */
    public function body(array $body): MessageInterface
    {
        $this->body = $body;

        return $this;
    }

    public function getBody(): string
    {
        return Json::encode($this->body);
    }
}
