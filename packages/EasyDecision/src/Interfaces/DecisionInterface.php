<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Interfaces;

interface DecisionInterface
{
    public const TYPES_YESNO = [
        self::TYPE_YESNO_AFFIRMATIVE,
        self::TYPE_YESNO_CONSENSUS,
        self::TYPE_YESNO_UNANIMOUS
    ];

    public const TYPE_VALUE = 'value';

    public const TYPE_YESNO_AFFIRMATIVE = 'yesno_affirmative';
    public const TYPE_YESNO_CONSENSUS = 'yesno_consensus';
    public const TYPE_YESNO_UNANIMOUS = 'yesno_unanimous';

    public function make($input);
}
