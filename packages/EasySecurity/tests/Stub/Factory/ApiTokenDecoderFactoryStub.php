<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stub\Factory;

use EonX\EasyApiToken\Common\Decoder\DecoderInterface;
use EonX\EasyApiToken\Common\Factory\ApiTokenDecoderFactoryInterface;
use Throwable;

final readonly class ApiTokenDecoderFactoryStub implements ApiTokenDecoderFactoryInterface
{
    public function __construct(
        private DecoderInterface|Throwable $decoder,
    ) {
    }

    public function build(?string $decoder = null): DecoderInterface
    {
        return $this->buildDefault();
    }

    public function buildDefault(): DecoderInterface
    {
        if ($this->decoder instanceof Throwable) {
            throw $this->decoder;
        }

        return $this->decoder;
    }

    public function reset(): void
    {
    }
}
