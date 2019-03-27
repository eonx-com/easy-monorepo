<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Exceptions;

use StepTheFkUp\ApiToken\Interfaces\ApiTokenExceptionInterface;

abstract class AbstractApiTokenException extends \Exception implements ApiTokenExceptionInterface
{
    // No body needed.
}
