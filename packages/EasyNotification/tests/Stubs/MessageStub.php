<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Stubs;

use EonX\EasyNotification\Messages\AbstractMessage;

final class MessageStub extends AbstractMessage
{
    /**
     * @var null|string
     */
    private $type;

    /**
     * @var mixed[] $body
     */
    public function __construct(array $body, ?string $type = null)
    {
        $this->type = $type;

        parent::__construct($body);
    }

    public function getType(): string
    {
        return $this->type ?? 'message_stub';
    }
}
