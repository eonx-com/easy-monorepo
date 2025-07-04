<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Fixture\App\EasyErrorHandler\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use EonX\EasyApiPlatform\Tests\Fixture\App\EasyErrorHandler\DataTransferObject\PaymentInputDtoWithConstructor;
use EonX\EasyApiPlatform\Tests\Fixture\App\EasyErrorHandler\StateProvider\PaymentStateProvider;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Uid\Uuid;

#[ApiResource(
    operations: [
        new Get(
            provider: PaymentStateProvider::class,
        ),
        new Post(),
        new Post(
            uriTemplate: 'payments-dto-with-constructor',
            input: PaymentInputDtoWithConstructor::class,
        ),
    ],
    openapi: false,
)]
final class Payment
{
    #[ApiProperty(identifier: true)]
    public Uuid $id;

    #[SerializedName('type')]
    public string $paymentType;

    public function __construct(string $paymentType)
    {
        $this->paymentType = $paymentType;
    }
}
