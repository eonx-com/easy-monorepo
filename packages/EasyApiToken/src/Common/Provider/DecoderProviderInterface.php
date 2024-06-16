<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Common\Provider;

use EonX\EasyUtils\Interfaces\HasPriorityInterface;

interface DecoderProviderInterface extends HasPriorityInterface
{
    /**
     * @return iterable<\EonX\EasyApiToken\Common\Decoder\DecoderInterface>
     */
    public function getDecoders(): iterable;

    public function getDefaultDecoder(): ?string;
}
