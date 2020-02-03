<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Laravel\Http\Middleware;

use Closure;
use EonX\EasySecurity\Interfaces\Resolvers\ContextResolverInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;

final class ContextResolverMiddleware
{
    /**
     * @var \Illuminate\Contracts\Foundation\Application
     */
    private $app;

    /**
     * @var \EonX\EasySecurity\Interfaces\Resolvers\ContextResolverInterface
     */
    private $resolver;

    /**
     * ContextResolverMiddleware constructor.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @param \EonX\EasySecurity\Interfaces\Resolvers\ContextResolverInterface $resolver
     */
    public function __construct(Application $app, ContextResolverInterface $resolver)
    {
        $this->app = $app;
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
        $this->app->instance(\config('easy-security.context_service_id'), $this->resolver->resolve($request));

        return $next($request);
    }
}
