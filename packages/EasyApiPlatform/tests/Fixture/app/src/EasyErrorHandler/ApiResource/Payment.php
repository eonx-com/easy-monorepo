<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Fixture\App\EasyErrorHandler\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use EonX\EasyApiPlatform\Tests\Fixture\App\EasyErrorHandler\DataTransferObject\PaymentInputDtoWithConstructor;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ApiResource(
    operations: [
        new Post(),
        new Post(
            uriTemplate: 'payments-dto-with-constructor',
            input: PaymentInputDtoWithConstructor::class,
        ),
    ]
)]
final class Payment
{
    #[SerializedName('type')]
    public string $paymentType;

    public function __construct(string $paymentType)
    {
        $this->paymentType = $paymentType;
    }
}
