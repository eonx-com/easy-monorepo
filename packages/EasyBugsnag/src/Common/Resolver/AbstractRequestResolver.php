<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Common\Resolver;

use Bugsnag\Request\ConsoleRequest;
use Bugsnag\Request\RequestInterface;
use Bugsnag\Request\ResolverInterface;
use EonX\EasyBugsnag\Common\Enum\ServerParam;

abstract class AbstractRequestResolver implements ResolverInterface
{
    private const IN_CLI = ['cli', 'phpdbg', 'embed'];

    public function resolve(): RequestInterface
    {
        $inCli = \in_array(\PHP_SAPI, self::IN_CLI, true);
        $resolveInCli = (bool)($_SERVER[ServerParam::ResolveRequestInCli->value] ?? false);

        if ($inCli && $resolveInCli === false) {
            /** @var string[] $argv */
            $argv = $_SERVER['argv'] ?? [];

            \array_shift($argv);

            return new ConsoleRequest($argv);
        }

        return $this->doResolve();
    }

    abstract protected function doResolve(): RequestInterface;
}
