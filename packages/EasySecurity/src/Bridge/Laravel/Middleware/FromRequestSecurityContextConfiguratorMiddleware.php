<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Laravel\Middleware;

use EonX\EasySecurity\Configurators\FromRequestConfigurator;
use EonX\EasySecurity\Interfaces\SecurityContextResolverInterface;
use Illuminate\Http\Request;

final class FromRequestSecurityContextConfiguratorMiddleware
{
    /**
     * @param iterable<\EonX\EasySecurity\Interfaces\SecurityContextConfiguratorInterface> $configurators
     */
    public function __construct(
        private SecurityContextResolverInterface $securityContextResolver,
        private iterable $configurators,
    ) {
    }

    public function handle(Request $request, \Closure $next): mixed
    {
        $this->securityContextResolver->setConfigurator(new FromRequestConfigurator(
            $request,
            $this->configurators
        ));

        return $next($request);
    }
}
