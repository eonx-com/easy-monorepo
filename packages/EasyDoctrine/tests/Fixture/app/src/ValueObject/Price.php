<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Fixture\App\ValueObject;

use Stringable;

final class Price implements Stringable
{
    public function __construct(
        private string $amount,
        private string $currency,
    ) {
    }

    public function __toString(): string
    {
        return \sprintf('%s %s', $this->amount, $this->currency);
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setAmount(string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }
}
