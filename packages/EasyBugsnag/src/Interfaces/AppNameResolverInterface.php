<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Interfaces;

interface AppNameResolverInterface
{
    public function resolveAppName(): ?string;
}
