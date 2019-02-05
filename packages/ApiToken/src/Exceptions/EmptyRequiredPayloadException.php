<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Exceptions;

use Exception;
use StepTheFkUp\ApiToken\Interfaces\ApiTokenExceptionInterface;

final class EmptyRequiredPayloadException extends Exception implements ApiTokenExceptionInterface
{
    // No body needed.
}