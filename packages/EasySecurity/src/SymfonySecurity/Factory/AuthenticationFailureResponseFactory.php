<?php
declare(strict_types=1);

namespace EonX\EasySecurity\SymfonySecurity\Factory;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

final readonly class AuthenticationFailureResponseFactory implements AuthenticationFailureResponseFactoryInterface
{
    public function __construct(
        private LoggerInterface $logger = new NullLogger(),
    ) {
    }

    public function create(Request $request, ?AuthenticationException $exception = null): Response
    {
        if ($exception !== null) {
            $this->logger->info('Authentication exception', [
                'data' => $exception->getMessageData(),
                'message' => $exception->getMessageKey(),
            ]);
        }

        $data = [
            'code' => JsonResponse::HTTP_UNAUTHORIZED,
            'message' => 'Unauthorized',
            'subCode' => 0,
        ];

        return new JsonResponse($data, JsonResponse::HTTP_UNAUTHORIZED);
    }
}
