<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Interfaces;

interface ValueInputInterface
{
    /**
     * Get value.
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Set value.
     *
     * @param mixed $value
     *
     * @return void
     */
    public function setValue($value): void;
}
