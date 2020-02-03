<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Laravel\Http\Middleware;

use Closure;
use EonX\EasySecurity\Interfaces\Resolvers\ContextResolverInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;

final class ContextResolverMiddleware
{
    /**
     * @var \Illuminate\Contracts\Container\Container
     */
    private $container;

    /**
     * @var \EonX\EasySecurity\Interfaces\Resolvers\ContextResolverInterface
     */
    private $resolver;

    /**
     * ContextResolverMiddleware constructor.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     * @param \EonX\EasySecurity\Interfaces\Resolvers\ContextResolverInterface $resolver
     */
    public function __construct(Container $container, ContextResolverInterface $resolver)
    {
        $this->container = $container;
        $this->resolver = $resolver;
    }

    /**
     * Resolver context and set it in app.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $this->container->instance(\config('easy-security.context_service_id'), $this->resolver->resolve($request));

        return $next($request);
    }
}
