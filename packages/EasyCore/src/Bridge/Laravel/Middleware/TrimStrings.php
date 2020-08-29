<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Laravel\Middleware;

use EonX\EasyCore\Helpers\StringsTrimmerInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

final class TrimStrings
{
    /**
     * A list of array keys whose values will be ignored during processing.
     * @see \EonX\EasyCore\Tests\Helpers\RecursiveStringsTrimmerTest for example
     *
     * @var string[]
     */
    private $except;

    /**
     * @var \EonX\EasyCore\Helpers\StringsTrimmerInterface
     */
    private $cleaner;

    /**
     * @param string[] $except
     */
    public function __construct(StringsTrimmerInterface $cleaner, array $except = [])
    {
        $this->cleaner = $cleaner;
        $this->except = $except;
    }

    /**
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        $this->clean($request);

        return $next($request);
    }

    private function clean(Request $request): void
    {
        $this->cleanParameterBag($request->query);

        if ($request->isJson()) {
            $this->cleanParameterBag($request->json());

            return;
        }

        if ($request->request !== $request->query) {
            $this->cleanParameterBag($request->request);
        }
    }

    private function cleanParameterBag(ParameterBag $bag): void
    {
        $bag->replace($this->cleaner->trim($bag->all(), $this->except));
    }
}
