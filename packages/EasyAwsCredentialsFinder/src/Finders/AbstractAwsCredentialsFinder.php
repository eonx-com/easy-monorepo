<?php

declare(strict_types=1);

namespace EonX\EasyAwsCredentialsFinder\Finders;

use EonX\EasyAwsCredentialsFinder\Interfaces\AwsCredentialsFinderInterface;

abstract class AbstractAwsCredentialsFinder implements AwsCredentialsFinderInterface
{
    /**
     * @var int
     */
    private $priority;

    public function __construct(?int $priority = null)
    {
        $this->priority = $priority ?? 0;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
}
