<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Decoders;

use EonX\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface;
use EonX\EasyApiToken\Interfaces\EasyApiTokenInterface;
use EonX\EasyApiToken\Traits\ChainEasyApiTokenDecoderTrait;
use Psr\Http\Message\ServerRequestInterface;

final class ChainReturnFirstTokenDecoder implements EasyApiTokenDecoderInterface
{
    use ChainEasyApiTokenDecoderTrait;

    /**
     * @var \EonX\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface[]
     */
    private $decoders;

    /**
     * ChainReturnFirstTokenDecoder constructor.
     *
     * @param mixed[] $decoders
     *
     * @throws \EonX\EasyApiToken\Exceptions\InvalidArgumentException
     */
    public function __construct(array $decoders)
    {
        $this->validateDecoders($decoders);

        $this->decoders = $decoders;
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
        foreach ($this->decoders as $decoder) {
            $token = $decoder->decode($request);

            if ($token !== null) {
                return $token;
            }
        }

        return null;
    }
}
