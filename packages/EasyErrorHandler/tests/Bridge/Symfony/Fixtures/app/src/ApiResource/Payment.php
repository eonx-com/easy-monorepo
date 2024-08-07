<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Bridge\Symfony\Fixtures\App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use EonX\EasyErrorHandler\Tests\Bridge\Symfony\Fixtures\App\DataTransferObject\PaymentInputDtoWithConstructor;
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
