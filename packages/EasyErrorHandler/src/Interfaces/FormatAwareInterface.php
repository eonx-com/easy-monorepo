<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Interfaces;

use Symfony\Component\HttpFoundation\Request;

interface FormatAwareInterface
{
    public function supportsFormat(Request $request): bool;
}
