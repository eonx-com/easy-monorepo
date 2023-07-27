<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

interface MessageSerializerInterface
{
    public function serialize(object $message): string;

    public function unserialize(string $message): object;
}
