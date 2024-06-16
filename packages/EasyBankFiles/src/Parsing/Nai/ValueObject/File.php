<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\Nai\ValueObject;

/**
 * @method FileHeader getHeader()
 * @method FileTrailer getTrailer()
 */
final class File extends AbstractNaiResult
{
    /**
     * Get file groups.
     *
     * @return \EonX\EasyBankFiles\Parsing\Nai\ValueObject\Group[]
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
