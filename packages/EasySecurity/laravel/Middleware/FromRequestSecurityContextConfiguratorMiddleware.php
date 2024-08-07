<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Laravel\Middleware;

use Closure;
use EonX\EasySecurity\Common\Configurator\FromRequestConfigurator;
use EonX\EasySecurity\Common\Resolver\SecurityContextResolverInterface;
use Illuminate\Http\Request;

final readonly class FromRequestSecurityContextConfiguratorMiddleware
{
    /**
     * @param iterable<\EonX\EasySecurity\Common\Configurator\SecurityContextConfiguratorInterface> $configurators
     */
    public function __construct(
        private SecurityContextResolverInterface $securityContextResolver,
        private iterable $configurators,
    ) {
    }

    public function handle(Request $request, Closure $next): mixed
    {
        $this->securityContextResolver->setConfigurator(new FromRequestConfigurator(
            $request,
            $this->configurators
        ));

        return $next($request);
    }
}
