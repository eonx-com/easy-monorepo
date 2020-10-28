<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Laravel\Http\Middleware;

use Closure;
use EonX\EasySecurity\Interfaces\SecurityContextResolverInterface;
use Illuminate\Http\Request;

/**
 * Not final on purpose for BC compatibility until 3.0.
 */
class SecurityContextResolverMiddleware
{
    /**
     * @var \EonX\EasySecurity\Interfaces\SecurityContextResolverInterface
     */
    private $resolver;

    public function __construct(SecurityContextResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $this->resolver->resolve($request);

        return $next($request);
    }
}
