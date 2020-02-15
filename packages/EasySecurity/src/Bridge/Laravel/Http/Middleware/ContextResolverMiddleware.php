<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Laravel\Http\Middleware;

use Closure;
use EonX\EasySecurity\Interfaces\ContextResolverInterface;
use Illuminate\Http\Request;

final class ContextResolverMiddleware
{
    /**
     * @var \EonX\EasySecurity\Interfaces\ContextResolverInterface
     */
    private $resolver;

    /**
     * ContextResolverMiddleware constructor.
     *
     * @param \EonX\EasySecurity\Interfaces\ContextResolverInterface $resolver
     */
    public function __construct(ContextResolverInterface $resolver)
    {
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
        $this->resolver->resolve($request);

        return $next($request);
    }
}
