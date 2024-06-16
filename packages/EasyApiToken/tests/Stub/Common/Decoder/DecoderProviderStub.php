<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Stub\Common\Decoder;

use EonX\EasyApiToken\Common\Decoder\BasicAuthDecoder;
use EonX\EasyApiToken\Common\Provider\DecoderProviderInterface;
use EonX\EasyUtils\Traits\HasPriorityTrait;

final class DecoderProviderStub implements DecoderProviderInterface
{
    use HasPriorityTrait;

    /**
     * @return iterable<\EonX\EasyApiToken\Common\Decoder\DecoderInterface>
     */
    public function getDecoders(): iterable
    {
        yield new BasicAuthDecoder();
    }

    public function getDefaultDecoder(): ?string
    {
        return BasicAuthDecoder::class;
    }
}
