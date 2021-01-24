<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Exceptions\Traits;

use EonX\EasyErrorHandler\Interfaces\Exceptions\SeverityAwareExceptionInterface;

trait SeverityAwareExceptionTrait
{
    /**
     * @var string
     */
    protected $severity = SeverityAwareExceptionInterface::SEVERITY_ERROR;

    public function getSeverity(): string
    {
        return $this->severity;
    }

    public function setSeverity(string $severity): self
    {
        $this->severity = $severity;

        return $this;
    }
}
