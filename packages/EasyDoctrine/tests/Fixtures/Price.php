<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Fixtures;

use Stringable;

final class Price implements Stringable
{
    private string $amount;

    private string $currency;

    public function __construct(string $amount, string $currency)
    {
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public function __toString()
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
