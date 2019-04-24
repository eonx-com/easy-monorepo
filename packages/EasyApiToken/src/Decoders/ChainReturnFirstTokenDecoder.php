<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Decoders;

use LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface;
use LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenInterface;
use LoyaltyCorp\EasyApiToken\Traits\ChainEasyApiTokenDecoderTrait;
use Psr\Http\Message\ServerRequestInterface;

final class ChainReturnFirstTokenDecoder implements EasyApiTokenDecoderInterface
{
    use ChainEasyApiTokenDecoderTrait;

    /**
     * @var \LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface[]
     */
    private $decoders;

    /**
     * ChainReturnFirstTokenDecoder constructor.
     *
     * @param mixed[] $decoders
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidArgumentException
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
     * @return null|\LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenInterface
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

\class_alias(
    ChainReturnFirstTokenDecoder::class,
    'StepTheFkUp\EasyApiToken\Decoders\ChainReturnFirstTokenDecoder',
    false
);
