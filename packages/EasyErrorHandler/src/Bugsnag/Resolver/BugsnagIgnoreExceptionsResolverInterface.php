<?php
declare(strict_types=1);

<<<<<<<< HEAD:packages/EasyErrorHandler/src/Bridge/EasyBugsnag/Interfaces/BugsnagExceptionIgnorerInterface.php
namespace EonX\EasyErrorHandler\Bridge\EasyBugsnag\Interfaces;
========
namespace EonX\EasyErrorHandler\Bugsnag\Resolver;
>>>>>>>> refs/heads/6.x:packages/EasyErrorHandler/src/Bugsnag/Resolver/BugsnagIgnoreExceptionsResolverInterface.php

use Throwable;

interface BugsnagExceptionIgnorerInterface
{
    public function shouldIgnore(Throwable $throwable): bool;
}
