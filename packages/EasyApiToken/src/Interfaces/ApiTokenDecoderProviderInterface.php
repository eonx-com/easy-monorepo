<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces;

use EonX\EasyUtils\Interfaces\HasPriorityInterface;

interface ApiTokenDecoderProviderInterface extends HasPriorityInterface
{
    /**
     * @return iterable<\EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface>
     */
    public function getDecoders(): iterable;

    public function getDefaultDecoder(): ?string;
}
