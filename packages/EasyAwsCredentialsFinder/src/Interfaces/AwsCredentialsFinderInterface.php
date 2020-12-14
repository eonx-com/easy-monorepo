<?php

declare(strict_types=1);

namespace EonX\EasyAwsCredentialsFinder\Interfaces;

use EonX\EasyUtils\Interfaces\HasPriorityInterface;

interface AwsCredentialsFinderInterface extends HasPriorityInterface
{
    public function findCredentials(): ?AwsCredentialsInterface;
}
