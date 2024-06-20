<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Common\Decoder;

use EonX\EasyApiToken\Common\ValueObject\ApiTokenInterface;
use Symfony\Component\HttpFoundation\Request;

interface DecoderInterface
{
    public function decode(Request $request): ?ApiTokenInterface;

    public function getName(): string;
}
