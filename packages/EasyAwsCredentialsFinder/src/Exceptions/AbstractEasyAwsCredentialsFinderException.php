<?php

declare(strict_types=1);

namespace EonX\EasyAwsCredentialsFinder\Exceptions;

use EonX\EasyAwsCredentialsFinder\Interfaces\EasyAwsCredentialsFinderExceptionInterface as ExceptionInterface;

abstract class AbstractEasyAwsCredentialsFinderException extends \RuntimeException implements ExceptionInterface
{
    // No body needed.
}
