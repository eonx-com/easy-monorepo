<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Stubs;

use EonX\EasyNotification\Messages\AbstractMessage;

final class MessageStub extends AbstractMessage
{
    /**
     * @param mixed[] $body
     */
    public function __construct(
        array $body,
        private ?string $type = null,
    ) {
        parent::__construct($body);
    }

    public function getType(): string
    {
        return $this->type ?? 'message_stub';
    }
}
