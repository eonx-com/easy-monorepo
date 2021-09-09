<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Decisions;

use EonX\EasyDecision\Exceptions\MissingValueIndexException;

final class ValueDecision extends AbstractDecision
{
    /**
     * @param mixed[] $input
     *
     * @return mixed
     */
    public function make(array $input)
    {
        if (isset($input['value']) === false) {
            throw new MissingValueIndexException($this->getExceptionMessage(
                'missing "value" index in given input'
            ));
        }

        return parent::make($input);
    }

    /**
     * @param mixed $output
     */
    protected function doHandleRuleOutput($output): void
    {
        // Update input for next rules with new value
        $this->input['value'] = $output;
    }

    /**
     * @return mixed
     */
    protected function doMake()
    {
        return $this->input['value'];
    }

    /**
     * @return mixed
     */
    protected function getDefaultOutput()
    {
        return $this->input['value'];
    }

    protected function reset(): void
    {
        // Nothing to do here
    }
}
