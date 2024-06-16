<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Generation\Common\Generator;

interface GeneratorInterface
{
    public const BREAK_LINE_MAC = "\r";

    public const BREAK_LINE_UNIX = "\n";

    public const BREAK_LINE_WINDOWS = "\r\n";

    public const VALIDATION_RULE_ALPHA = 'alpha';

    public const VALIDATION_RULE_BSB = 'bsb';

    public const VALIDATION_RULE_DATE = 'date';

    public const VALIDATION_RULE_NUMERIC = 'numeric';

    /**
     * Return contents.
     */
    public function getContents(): string;
}
