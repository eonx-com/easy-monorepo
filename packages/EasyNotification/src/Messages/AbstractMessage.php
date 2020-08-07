<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Messages;

use EonX\EasyNotification\Interfaces\MessageInterface;
use Nette\Utils\Json;

abstract class AbstractMessage implements MessageInterface
{
    /**
     * @var mixed[]
     */
    private $body;

    /**
     * @param string[] $body
     */
    public function __construct(array $body)
    {
        $this->body = $body;
    }

    public function getBody(): string
    {
        return Json::encode($this->body);
    }
}
