<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Laravel\Middleware;

use EonX\EasyCore\Tests\Helpers\CleanerInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

final class TrimStrings
{
    /**
     * The attributes that should not be trimmed.
     *
     * @var mixed[]
     */
    private $except;

    /**
     * @var CleanerInterface
     */
    private $cleaner;

    public function __construct(CleanerInterface $cleaner, array $except = [])
    {
        $this->cleaner = $cleaner;
        $this->except = $except;
    }

    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        $this->clean($request);

        return $next($request);
    }

    /**
     * Clean the request's data.
     */
    private function clean(Request $request): void
    {
        $this->cleanParameterBag($request->query);

        if ($request->isJson()) {
            $this->cleanParameterBag($request->json());
        } elseif ($request->request !== $request->query) {
            $this->cleanParameterBag($request->request);
        }
    }

    /**
     * Clean the data in the parameter bag.
     */
    private function cleanParameterBag(ParameterBag $bag): void
    {
        $bag->replace($this->cleaner->clean($bag->all(), $this->except));
    }
}
