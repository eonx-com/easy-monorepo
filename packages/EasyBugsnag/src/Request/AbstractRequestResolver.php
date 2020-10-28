<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Request;

use Bugsnag\Request\ConsoleRequest;
use Bugsnag\Request\RequestInterface;
use Bugsnag\Request\ResolverInterface;

abstract class AbstractRequestResolver implements ResolverInterface
{
    /**
     * @var string[]
     */
    protected static $inConsole = ['cli', 'phpdbg'];

    public function resolve(): RequestInterface
    {
        if (\in_array(\PHP_SAPI, static::$inConsole, true)) {
            $argv = $_SERVER['argv'] ?? [];

            \array_shift($argv);

            return new ConsoleRequest($argv);
        }

        return $this->doResolve();
    }

    abstract protected function doResolve(): RequestInterface;
}
