<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Fixture\App\EasyErrorHandler\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use EonX\EasyApiPlatform\Tests\Fixture\App\EasyErrorHandler\DataTransferObject\InvoiceInputDtoWithConstructor;

#[ApiResource(
    operations: [
        new Post(
            input: InvoiceInputDtoWithConstructor::class
        ),
    ],
    openapi: false,
)]
final class Invoice
{
    public function __construct(
        public Payment $payment,
    ) {
    }
}
