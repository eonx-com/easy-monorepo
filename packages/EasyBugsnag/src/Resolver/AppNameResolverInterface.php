<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Resolver;

interface AppNameResolverInterface
{
    public function resolveAppName(): ?string;
}
