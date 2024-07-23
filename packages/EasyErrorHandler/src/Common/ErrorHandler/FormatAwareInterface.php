<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\ErrorHandler;

use Symfony\Component\HttpFoundation\Request;

interface FormatAwareInterface
{
    public function supportsFormat(Request $request): bool;
}
