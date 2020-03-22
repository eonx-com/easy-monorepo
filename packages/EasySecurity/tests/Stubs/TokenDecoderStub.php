<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stubs;

use EonX\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface;
use EonX\EasyApiToken\Interfaces\EasyApiTokenInterface;
use Psr\Http\Message\ServerRequestInterface;

final class TokenDecoderStub implements EasyApiTokenDecoderInterface
{
    /**
     * @var null|\EonX\EasyApiToken\Interfaces\EasyApiTokenInterface
     */
    private $token;

    public function __construct(?EasyApiTokenInterface $token = null)
    {
        $this->token = $token;
    }

    public function decode(ServerRequestInterface $request): ?EasyApiTokenInterface
    {
        return $this->token;
    }
}
