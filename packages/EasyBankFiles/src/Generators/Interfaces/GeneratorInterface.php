<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Generators\Interfaces;

interface GeneratorInterface
{
    /**
     * @const string
     */
    public const BREAK_LINE_MAC = "\r";

    /**
     * @const string
     */
    public const BREAK_LINE_UNIX = "\n";

    /**
     * @const string
     */
    public const BREAK_LINE_WINDOWS = "\r\n";

    /**
     * @const string
     */
    public const VALIDATION_RULE_ALPHA = 'alpha';

    /**
     * @const string
     */
    public const VALIDATION_RULE_BSB = 'bsb';

    /**
     * @const string
     */
    public const VALIDATION_RULE_DATE = 'date';

    /**
     * @const string
     */
    public const VALIDATION_RULE_NUMERIC = 'numeric';

    /**
     * Return contents.
     */
    public function getContents(): string;
}
