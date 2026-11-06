<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Stub\Message;

final class UnserializeTrackingMessageStub
{
    private static bool $unserialized = false;

    public function __construct(
        private string $value = 'value',
    ) {}

    public function __unserialize(array $data): void
    {
        self::$unserialized = true;
        $this->value = (string)$data["\0" . self::class . "\0value"];
    }

    public static function isUnserialized(): bool
    {
        return self::$unserialized;
    }

    public static function reset(): void
    {
        self::$unserialized = false;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
