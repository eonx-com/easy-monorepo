<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Common\Decoder;

use EonX\EasyApiToken\Common\ValueObject\ApiTokenInterface;
use EonX\EasyUtils\Common\Helper\CollectorHelper;
use Symfony\Component\HttpFoundation\Request;

final class ChainDecoder extends AbstractDecoder
{
    /**
     * @var \EonX\EasyApiToken\Common\Decoder\DecoderInterface[]
     */
    private readonly array $decoders;

    public function __construct(iterable $decoders, ?string $name = null)
    {
        /** @var \EonX\EasyApiToken\Common\Decoder\DecoderInterface[] $filteredDecoders */
        $filteredDecoders = CollectorHelper::filterByClassAsArray($decoders, DecoderInterface::class);
        $this->decoders = $filteredDecoders;

        parent::__construct($name);
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
