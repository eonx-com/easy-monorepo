<?php

declare(strict_types=1);

namespace EonX\EasyEncryption\Resolvers;

use EonX\EasyEncryption\Interfaces\EncryptionKeyResolverInterface;
use EonX\EasyUtils\Traits\HasPriorityTrait;

abstract class AbstractEncryptionKeyResolver implements EncryptionKeyResolverInterface
{
    use HasPriorityTrait;
}
