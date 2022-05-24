<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\EasyUtils\Exceptions;

use EonX\EasyBugsnag\Interfaces\EasyBugsnagExceptionInterface;

final class EasyUtilsNotInstalledException extends \RuntimeException implements EasyBugsnagExceptionInterface
{
}
