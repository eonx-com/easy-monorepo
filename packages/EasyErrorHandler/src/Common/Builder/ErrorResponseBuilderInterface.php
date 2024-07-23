<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Builder;

use EonX\EasyUtils\Common\Enum\HttpStatusCode;
use EonX\EasyUtils\Common\Helper\HasPriorityInterface;
use Throwable;

interface ErrorResponseBuilderInterface extends HasPriorityInterface
{
    public function buildData(Throwable $throwable, array $data): array;

    public function buildHeaders(Throwable $throwable, ?array $headers = null): ?array;

    public function buildStatusCode(Throwable $throwable, ?HttpStatusCode $statusCode = null): ?HttpStatusCode;
}
