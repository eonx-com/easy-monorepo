<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Exception;

use BackedEnum;
use InvalidArgumentException;

trait SubCodeAwareExceptionTrait
{
    protected int $subCode = 0;

    public function getSubCode(): int
    {
        return $this->subCode;
    }

    /**
     * Sets the sub code for an exception.
     */
    public function setSubCode(int|BackedEnum $subCode): self
    {
        if ($subCode instanceof BackedEnum && \is_int($subCode->value) === false) {
            throw new InvalidArgumentException(
                \sprintf('The backed case of the "%s" backed enum has to be an integer.', $subCode::class)
            );
        }

        if ($subCode instanceof BackedEnum && \is_int($subCode->value)) {
            $this->subCode = $subCode->value;

            return $this;
        }

        if (\is_int($subCode)) {
            $this->subCode = $subCode;
        }

        return $this;
    }
}
