<?php
declare(strict_types=1);

namespace EonX\EasyTest\Tests\Fixture\Message;

final readonly class DummyMessage
{
    public function __construct(
        private string $name = 'dummy',
    ) {}

    public function getName(): string
    {
        return $this->name;
    }
}
