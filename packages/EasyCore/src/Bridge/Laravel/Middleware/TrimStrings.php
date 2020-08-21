<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Laravel\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

final class TrimStrings
{
    /**
     * The attributes that should not be trimmed.
     *
     * @var mixed[]
     */
    private $except = [];

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
        $bag->replace($this->cleanArray($bag->all()));
    }

    /**
     * Clean the data in the given array.
     *
     * @return mixed[]
     */
    private function cleanArray(array $data, string $keyPrefix = ''): array
    {
        return \collect($data)->map(function ($value, $key) use ($keyPrefix) {
            return $this->cleanValue($keyPrefix . $key, $value);
        })->all();
    }

    /**
     * Clean the given value.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    private function cleanValue(string $key, $value)
    {
        if (\is_array($value)) {
            return $this->cleanArray($value, $key . '.');
        }

        return $this->transform($key, $value);
    }

    /**
     * Transform the given value.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    private function transform(string $key, $value)
    {
        if (\in_array($key, $this->except, true)) {
            return $value;
        }

        return \is_string($value) ? \trim($value) : $value;
    }
}
