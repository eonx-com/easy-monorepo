<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Laravel\ApiFormats\Middleware;

use EonX\EasyCore\Bridge\Laravel\ApiFormats\Interfaces\FormattedApiResponseInterface;
use EonX\EasyCore\Bridge\Laravel\ApiFormats\Interfaces\SerializableInterface;
use EonX\EasyCore\Bridge\Laravel\ApiFormats\Responses\NoContentApiResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;

final class ReplaceApiFormatsMiddleware
{
    /**
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        $decoded = \json_decode((string)$request->getContent(), true);

        if (\is_array($decoded)) {
            $request->request = \class_exists(InputBag::class)
                ? new InputBag($decoded)
                : new ParameterBag($decoded);
        }

        $response = $next($request);

        if ($response instanceof FormattedApiResponseInterface) {
            return $response instanceof NoContentApiResponse ?
                $response :
                new JsonResponse(
                    $this->getResponseAsArray($response->getContent()),
                    $response->getStatusCode(),
                    $response->getHeaders()
                );
        }

        // Symfony Response
        if ($response instanceof Response) {
            return $response;
        }

        return new JsonResponse((array)$response);
    }

    /**
     * @param mixed $response
     *
     * @return mixed[]
     */
    private function getResponseAsArray($response): array
    {
        return $response instanceof SerializableInterface ? $response->toArray() : (array)$response;
    }
}
