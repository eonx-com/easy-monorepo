<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Fixture\App\ValueObject;

final class AuthorExtra
{
    private string $phone;

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): AuthorExtra
    {
        $this->phone = $phone;

        return $this;
    }
}
