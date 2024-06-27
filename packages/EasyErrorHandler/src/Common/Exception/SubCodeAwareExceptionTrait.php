<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Exception;

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
    public function setSubCode(int $subCode): self
    {
        $this->subCode = $subCode;

        return $this;
    }
}
