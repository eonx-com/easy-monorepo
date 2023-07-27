<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stubs;

use EonX\EasyApiToken\Decoders\BasicAuthDecoder;
use EonX\EasyApiToken\Interfaces\ApiTokenDecoderProviderInterface;
use EonX\EasyUtils\Traits\HasPriorityTrait;

final class ApiTokenDecoderProviderStub implements ApiTokenDecoderProviderInterface
{
    use HasPriorityTrait;

    /**
     * @return iterable<\EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface>
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
