<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Stub\Message;

final class MessageStub
{
    public function __construct(
        public string $content = 'content',
    ) {}
}
