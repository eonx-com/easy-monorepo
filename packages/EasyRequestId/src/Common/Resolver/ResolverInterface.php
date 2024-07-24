<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Common\Resolver;

use EonX\EasyRequestId\Common\ValueObject\RequestIdInfo;

interface ResolverInterface
{
    public function __invoke(): RequestIdInfo;
}
