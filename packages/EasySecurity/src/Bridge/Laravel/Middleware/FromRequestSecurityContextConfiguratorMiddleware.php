<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Laravel\Middleware;

use EonX\EasySecurity\Configurators\FromRequestConfigurator;
use EonX\EasySecurity\Interfaces\SecurityContextResolverInterface;
use Illuminate\Http\Request;

final class FromRequestSecurityContextConfiguratorMiddleware
{
    /**
     * @var iterable<\EonX\EasySecurity\Interfaces\SecurityContextConfiguratorInterface>
     */
    private $configurators;

    /**
     * @var \EonX\EasySecurity\Interfaces\SecurityContextResolverInterface
     */
    private $securityContextResolver;

    /**
     * @param iterable<\EonX\EasySecurity\Interfaces\SecurityContextConfiguratorInterface> $configurators
     */
    public function __construct(SecurityContextResolverInterface $securityContextResolver, iterable $configurators)
    {
        $this->securityContextResolver = $securityContextResolver;
        $this->configurators = $configurators;
    }

    /**
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        $this->securityContextResolver->setConfigurator(new FromRequestConfigurator(
            $request,
            $this->configurators,
        ));

        return $next($request);
    }
}
