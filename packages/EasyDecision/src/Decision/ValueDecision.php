<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Decision;

use EonX\EasyDecision\Exception\MissingValueIndexException;

final class ValueDecision extends AbstractDecision
{
    public function make(array $input): mixed
    {
        if (isset($input['value']) === false) {
            throw new MissingValueIndexException($this->getExceptionMessage(
                'missing "value" index in given input'
            ));
        }

        return parent::make($input);
    }

    protected function doHandleRuleOutput(mixed $output): void
    {
        // Update input for next rules with new value
        $this->input['value'] = $output;
    }

    protected function doMake(): mixed
    {
        return $this->input['value'];
    }

    protected function getDefaultOutput(): mixed
    {
        return $this->input['value'];
    }

    protected function reset(): void
    {
        // Nothing to do here
    }
}
