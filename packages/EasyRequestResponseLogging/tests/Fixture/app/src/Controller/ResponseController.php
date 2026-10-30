<?php
declare(strict_types=1);

namespace EonX\EasyRequestResponseLogging\Tests\Fixture\App\Controller;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class ResponseController
{
    public const READ_BODY_AS_STREAM_HEADER = 'X-Read-Body-As-Stream';

    public function __invoke(Request $request): JsonResponse
    {
        if ($request->headers->has(self::READ_BODY_AS_STREAM_HEADER)) {
            \stream_get_contents($request->getContent(true));
        }

        $response = JsonResponse::fromJsonString(
            '{"card_token":"some-card-token","float_note":4111111111111111.0,"name":"John","note":4111111111111111}'
        );
        $response->headers->setCookie(Cookie::create('session', 'some-session'));

        return $response;
    }
}
