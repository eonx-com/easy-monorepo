<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Decoders;

use EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface;
use EonX\EasyApiToken\Interfaces\ApiTokenInterface;

final class ChainDecoder extends AbstractApiTokenDecoder
{
    /**
     * @var \EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface[]
     */
    private $decoders;

    /**
     * @param mixed[] $decoders
     */
    public function __construct(array $decoders, ?string $name = null)
    {
        $this->decoders = $this->filterDecoders($decoders);

        parent::__construct($name ?? self::NAME_CHAIN);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request|\Psr\Http\Message\ServerRequestInterface $request
     */
    public function decode($request): ?ApiTokenInterface
    {
        foreach ($this->decoders as $decoder) {
            $token = $decoder->decode($request);

            if ($token !== null) {
                return $token;
            }
        }

        return null;
    }

    /**
     * @param mixed[] $decoders
     *
     * @return \EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface[]
     */
    private function filterDecoders(array $decoders): array
    {
        return \array_filter($decoders, static function ($decoder): bool {
            return $decoder instanceof ApiTokenDecoderInterface;
        });
    }
}

\class_alias(ChainDecoder::class, ChainReturnFirstTokenDecoder::class);
