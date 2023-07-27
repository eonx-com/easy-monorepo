<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stubs;

use EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface;
use EonX\EasyApiToken\Interfaces\ApiTokenInterface;
use Symfony\Component\HttpFoundation\Request;

final class TokenDecoderStub implements ApiTokenDecoderInterface
{
    public function __construct(
        private ?ApiTokenInterface $token = null,
    ) {
    }

    public function decode(Request $request): ?ApiTokenInterface
    {
        return $this->token;
    }

    public function getName(): string
    {
        return 'decoder';
    }
}
