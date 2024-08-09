<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Common\Resolver;

interface AppNameResolverInterface
{
    public function resolveAppName(): ?string;
}
