<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Logging\Formatter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface HttpFoundationAccessLogFormatterInterface
{
    public function formatAccessLog(Request $request, Response $response): string;
}
