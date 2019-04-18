<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyApiToken\Decoders;

use Psr\Http\Message\ServerRequestInterface;
use StepTheFkUp\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface;
use StepTheFkUp\EasyApiToken\Interfaces\EasyApiTokenInterface;
use StepTheFkUp\EasyApiToken\Traits\ChainEasyApiTokenDecoderTrait;

final class ChainReturnFirstTokenDecoder implements EasyApiTokenDecoderInterface
{
    use ChainEasyApiTokenDecoderTrait;

    /**
     * @var \StepTheFkUp\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface[]
     */
    private $decoders;

    /**
     * ChainReturnFirstTokenDecoder constructor.
     *
     * @param mixed[] $decoders
     *
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\InvalidArgumentException
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
     * @return null|\StepTheFkUp\EasyApiToken\Interfaces\EasyApiTokenInterface
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
    'LoyaltyCorp\EasyApiToken\Decoders\ChainReturnFirstTokenDecoder',
    false
);
