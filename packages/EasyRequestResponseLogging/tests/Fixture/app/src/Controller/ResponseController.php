<?php
declare(strict_types=1);

namespace EonX\EasyRequestResponseLogging\Tests\Fixture\App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

final class ResponseController
{
    public function __invoke(): JsonResponse
    {
        return new JsonResponse([
            'card_token' => 'some-card-token',
            'name' => 'John',
        ]);
    }
}
