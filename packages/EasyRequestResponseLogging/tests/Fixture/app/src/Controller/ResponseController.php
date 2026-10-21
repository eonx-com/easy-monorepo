<?php
declare(strict_types=1);

namespace EonX\EasyRequestResponseLogging\Tests\Fixture\App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

final class ResponseController
{
    public function __invoke(): JsonResponse
    {
        return JsonResponse::fromJsonString(
            '{"card_token":"some-card-token","float_note":4111111111111111.0,"name":"John","note":4111111111111111}'
        );
    }
}
