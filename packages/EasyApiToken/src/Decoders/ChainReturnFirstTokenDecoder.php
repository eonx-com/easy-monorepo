<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Decoders;

use EonX\EasyApiToken\Interfaces\ApiTokenInterface;
use EonX\EasyApiToken\Traits\ChainEasyApiTokenDecoderTrait;
use Psr\Http\Message\ServerRequestInterface;

final class ChainReturnFirstTokenDecoder extends AbstractApiTokenDecoder
{
    use ChainEasyApiTokenDecoderTrait;

    /**
     * @var \EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface[]
     */
    private $decoders;

    /**
     * @param mixed[] $decoders
     *
     * @throws \EonX\EasyApiToken\Exceptions\InvalidArgumentException
     */
    public function __construct(array $decoders, ?string $name = null)
    {
        $this->validateDecoders($decoders);

        $this->decoders = $decoders;

        parent::__construct($name ?? self::NAME_CHAIN);
    }

    public function decode(ServerRequestInterface $request): ?ApiTokenInterface
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
