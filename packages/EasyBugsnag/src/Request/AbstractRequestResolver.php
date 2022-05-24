<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Request;

use Bugsnag\Request\ConsoleRequest;
use Bugsnag\Request\RequestInterface;
use Bugsnag\Request\ResolverInterface;
use EonX\EasyBugsnag\Interfaces\ValueOptionInterface;

abstract class AbstractRequestResolver implements ResolverInterface
{
    /**
     * @var string[]
     */
    private const IN_CLI = ['cli', 'phpdbg', 'embed'];

    public function resolve(): RequestInterface
    {
        $inCli = \in_array(\PHP_SAPI, self::IN_CLI, true);
        $resolveInCli = (bool)($_SERVER[ValueOptionInterface::RESOLVE_REQUEST_IN_CLI] ?? false);

        if ($inCli && $resolveInCli === false) {
            $argv = $_SERVER['argv'] ?? [];

            \array_shift($argv);

            return new ConsoleRequest($argv);
        }

        return $this->doResolve();
    }

    abstract protected function doResolve(): RequestInterface;
}
