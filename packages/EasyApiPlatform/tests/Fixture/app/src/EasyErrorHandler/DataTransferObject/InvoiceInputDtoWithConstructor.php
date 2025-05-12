<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Fixture\App\EasyErrorHandler\DataTransferObject;

use EonX\EasyApiPlatform\Tests\Fixture\App\EasyErrorHandler\ApiResource\Payment;

final readonly class InvoiceInputDtoWithConstructor
{
    public function __construct(
        private Payment $payment,
    ) {
    }

    public function getPayment(): Payment
    {
        return $this->payment;
    }
}
