<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Exceptions;

use EonX\EasyErrorHandler\Exceptions\Traits\LogLevelAwareExceptionTrait;
use EonX\EasyErrorHandler\Exceptions\Traits\StatusCodeAwareExceptionTrait;
use EonX\EasyErrorHandler\Exceptions\Traits\SubCodeAwareExceptionTrait;
use EonX\EasyErrorHandler\Exceptions\Traits\TranslatableExceptionTrait;
use EonX\EasyErrorHandler\Interfaces\Exceptions\LogLevelAwareExceptionInterface;
use EonX\EasyErrorHandler\Interfaces\Exceptions\StatusCodeAwareExceptionInterface;
use EonX\EasyErrorHandler\Interfaces\Exceptions\SubCodeAwareExceptionInterface;
use EonX\EasyErrorHandler\Interfaces\Exceptions\TranslatableExceptionInterface;
use Exception;

abstract class BaseException extends Exception implements
    TranslatableExceptionInterface,
    LogLevelAwareExceptionInterface,
    StatusCodeAwareExceptionInterface,
    SubCodeAwareExceptionInterface
{
    use LogLevelAwareExceptionTrait;
    use StatusCodeAwareExceptionTrait;
    use SubCodeAwareExceptionTrait;
    use TranslatableExceptionTrait;
}
