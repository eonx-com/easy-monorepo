<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Exception;

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
