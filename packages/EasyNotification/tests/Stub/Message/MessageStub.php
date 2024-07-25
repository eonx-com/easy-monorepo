<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Stub\Message;

use EonX\EasyNotification\Enum\Type;
use EonX\EasyNotification\Message\AbstractMessage;

final class MessageStub extends AbstractMessage
{
    public function __construct(
        array $body,
        private ?Type $type = null,
    ) {
        parent::__construct($body);
    }

    public function getType(): Type
    {
        return $this->type ?? Type::Push;
    }
}
