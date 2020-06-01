<?php
declare(strict_types=1);

namespace EonX\EasyAwsCredentialsFinder\Interfaces;

interface AwsCredentialsProviderInterface
{
    public function getCredentials(): AwsCredentialsInterface;
}
