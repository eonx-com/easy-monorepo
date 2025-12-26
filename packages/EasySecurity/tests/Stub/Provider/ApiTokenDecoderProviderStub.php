<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stub\Provider;

use EonX\EasyApiToken\Common\Decoder\BasicAuthDecoder;
use EonX\EasyApiToken\Common\Provider\DecoderProviderInterface;
use EonX\EasyUtils\Common\Helper\HasPriorityTrait;

final class ApiTokenDecoderProviderStub implements DecoderProviderInterface
{
    use HasPriorityTrait;

    /**
     * @return iterable<\EonX\EasyApiToken\Common\Decoder\DecoderInterface>
     */
    public function getDecoders(): iterable
    {
        yield new BasicAuthDecoder();
    }

    public function getDefaultDecoder(): string
    {
        return BasicAuthDecoder::class;
    }
}
