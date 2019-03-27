<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Decoders;

use Psr\Http\Message\ServerRequestInterface;
use StepTheFkUp\ApiToken\Interfaces\ApiTokenDecoderInterface;
use StepTheFkUp\ApiToken\Interfaces\ApiTokenInterface;
use StepTheFkUp\ApiToken\Traits\ChainApiTokenDecoderTrait;

final class ChainReturnFirstTokenDecoder implements ApiTokenDecoderInterface
{
    use ChainApiTokenDecoderTrait;

    /**
     * @var \StepTheFkUp\ApiToken\Interfaces\ApiTokenDecoderInterface[]
     */
    private $decoders;

    /**
     * ChainReturnFirstTokenDecoder constructor.
     *
     * @param mixed[] $decoders
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\InvalidArgumentException
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
     * @return null|\StepTheFkUp\ApiToken\Interfaces\ApiTokenInterface
     */
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
