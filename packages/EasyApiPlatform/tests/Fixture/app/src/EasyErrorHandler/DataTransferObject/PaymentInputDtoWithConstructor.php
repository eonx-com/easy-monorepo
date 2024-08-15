<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Fixture\App\EasyErrorHandler\DataTransferObject;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class PaymentInputDtoWithConstructor
{
    public function __construct(
        #[SerializedName('type')]
        public string $paymentType,
    ) {
    }
}
