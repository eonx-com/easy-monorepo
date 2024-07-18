<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Fixture\App\DataTransferObject;

final readonly class Author
{
    public function __construct(
        private string $name,
        private int $age,
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
