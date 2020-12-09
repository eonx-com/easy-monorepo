<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Generators\Interfaces;

interface GeneratorInterface
{
    /**
     * @var string
     */
    public const BREAK_LINE_MAC = "\r";

    /**
     * @var string
     */
    public const BREAK_LINE_UNIX = "\n";

    /**
     * @var string
     */
    public const BREAK_LINE_WINDOWS = "\r\n";

    /**
     * @var string
     */
    public const VALIDATION_RULE_ALPHA = 'alpha';

    /**
     * @var string
     */
    public const VALIDATION_RULE_BSB = 'bsb';

    /**
     * @var string
     */
    public const VALIDATION_RULE_DATE = 'date';

    /**
     * @var string
     */
    public const VALIDATION_RULE_NUMERIC = 'numeric';

    /**
     * Return contents.
     */
    public function getContents(): string;
}
