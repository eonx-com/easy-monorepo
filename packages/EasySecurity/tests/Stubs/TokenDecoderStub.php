<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stubs;

use EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface;
use EonX\EasyApiToken\Interfaces\ApiTokenInterface;
use Psr\Http\Message\ServerRequestInterface;

final class TokenDecoderStub implements ApiTokenDecoderInterface
{
    /**
     * @var null|\EonX\EasyApiToken\Interfaces\ApiTokenInterface
     */
    private $token;

    public function __construct(?ApiTokenInterface $token = null)
    {
        $this->token = $token;
    }

    public function decode(ServerRequestInterface $request): ?ApiTokenInterface
    {
        return $this->token;
    }

    public function getName(): string
    {
        return 'decoder';
    }
}
