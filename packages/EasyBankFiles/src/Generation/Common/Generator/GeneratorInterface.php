<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Generation\Common\Generator;

interface GeneratorInterface
{
    /**
     * Return contents.
     */
    public function getContents(): string;
}
