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

    /**
     * TokenDecoderStub constructor.
     *
     * @param null|\EonX\EasyApiToken\Interfaces\EasyApiTokenInterface $token
     */
    public function __construct(?EasyApiTokenInterface $token = null)
    {
        $this->token = $token;
    }

    /**
     * Decode API token for given request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return null|\EonX\EasyApiToken\Interfaces\EasyApiTokenInterface
     */
    public function decode(ServerRequestInterface $request): ?EasyApiTokenInterface
    {
        return $this->token;
    }
}
