<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Common\Factory;

use EonX\EasyApiToken\Common\Decoder\DecoderInterface;

interface ApiTokenDecoderFactoryInterface
{
    public function build(?string $decoder = null): DecoderInterface;

    public function buildDefault(): DecoderInterface;

    public function reset(): void;
}
