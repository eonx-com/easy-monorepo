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
     *
     * @see \EonX\EasyCore\Tests\Helpers\RecursiveStringsTrimmerTest for example
     *
     * @var string[]
     */
    private $except;

    /**
     * @var \EonX\EasyCore\Helpers\StringsTrimmerInterface
     */
    private $trimmer;

    /**
     * @param string[]|null $except
     */
    public function __construct(StringsTrimmerInterface $trimmer, ?array $except = null)
    {
        $this->trimmer = $trimmer;
        $this->except = $except ?? [];
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

    /**
     * @param \Symfony\Component\HttpFoundation\ParameterBag<string, mixed> $bag
     */
    private function cleanParameterBag(ParameterBag $bag): void
    {
        $bag->replace($this->trimmer->trim($bag->all(), $this->except));
    }
}
