<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\Nai\Results;

use EonX\EasyBankFiles\Parsers\Nai\Results\Files\Header;
use EonX\EasyBankFiles\Parsers\Nai\Results\Files\Trailer;

/**
 * @method Header getHeader()
 * @method Trailer getTrailer()
 */
final class File extends AbstractNaiResult
{
    /**
     * Get file groups.
     *
     * @return \EonX\EasyBankFiles\Parsers\Nai\Results\Group[]
     */
    public function getGroups(): array
    {
        return $this->context->getGroups();
    }

    /**
     * Return object attributes.
     *
     * @return string[]
     */
    protected function initAttributes(): array
    {
        return ['header', 'trailer'];
    }
}
