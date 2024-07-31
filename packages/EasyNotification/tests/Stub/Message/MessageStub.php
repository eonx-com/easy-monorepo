<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Stub\Message;

use EonX\EasyNotification\Enum\MessageType;
use EonX\EasyNotification\Message\AbstractMessage;

final class MessageStub extends AbstractMessage
{
    public function __construct(
        array $body,
        private readonly ?MessageType $type = null,
    ) {
        parent::__construct($body);
    }

    public function getType(): MessageType
    {
        return $this->type ?? MessageType::Push;
    }
}
