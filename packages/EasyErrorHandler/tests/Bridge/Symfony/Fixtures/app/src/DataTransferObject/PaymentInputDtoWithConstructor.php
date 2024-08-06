<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Bridge\Symfony\Fixtures\App\DataTransferObject;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class PaymentInputDtoWithConstructor
{
    public function __construct(
        #[SerializedName('type')]public string $paymentType,
    ) {
    }
}
