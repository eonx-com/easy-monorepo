<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Decoders;

use EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface;
use EonX\EasyApiToken\Interfaces\ApiTokenInterface;
use EonX\EasyUtils\Helpers\CollectorHelper;
use Symfony\Component\HttpFoundation\Request;

final class ChainDecoder extends AbstractApiTokenDecoder
{
    /**
     * @var iterable<\EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface>
     */
    private $decoders;

    /**
     * @param mixed[] $decoders
     */
    public function __construct(array $decoders, ?string $name = null)
    {
        $this->decoders = CollectorHelper::filterByClassAsArray($decoders, ApiTokenDecoderInterface::class);

        parent::__construct($name ?? self::NAME_CHAIN);
    }

    public function decode(Request $request): ?ApiTokenInterface
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
