<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Bridge\Symfony\Fixtures\App\DataTransferObject;

final class Author
{
    public function __construct(
        private readonly string $name,
        private readonly int $age,
    ) {
    }

    public function getAge(): int
    {
        return $this->age;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
