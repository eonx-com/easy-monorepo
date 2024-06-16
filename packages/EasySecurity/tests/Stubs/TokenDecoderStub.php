<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stubs;

use EonX\EasyApiToken\Common\Decoder\DecoderInterface;
use EonX\EasyApiToken\Common\ValueObject\ApiTokenInterface;
use Symfony\Component\HttpFoundation\Request;

final class TokenDecoderStub implements DecoderInterface
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
