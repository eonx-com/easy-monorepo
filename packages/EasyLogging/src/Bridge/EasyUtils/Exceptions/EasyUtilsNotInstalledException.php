<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Bridge\EasyUtils\Exceptions;

use EonX\EasyLogging\Interfaces\EasyLoggingExceptionInterface;

final class EasyUtilsNotInstalledException extends \RuntimeException implements EasyLoggingExceptionInterface
{
}
