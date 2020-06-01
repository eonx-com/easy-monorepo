<?php
declare(strict_types=1);

namespace EonX\EasyAwsCredentialsFinder\Interfaces;

interface AwsCredentialsFinderInterface
{
    public function findCredentials(): ?AwsCredentialsInterface;

    public function getPriority(): int;
}
