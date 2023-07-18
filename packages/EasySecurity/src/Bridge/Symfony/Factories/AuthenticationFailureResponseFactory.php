<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\Factories;

use EonX\EasySecurity\Bridge\Symfony\Interfaces\AuthenticationFailureResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

final class AuthenticationFailureResponseFactory implements AuthenticationFailureResponseFactoryInterface
{
    public function __construct(
        private LoggerInterface $logger = new NullLogger(),
    ) {
    }

    public function create(Request $request, ?AuthenticationException $exception = null): Response
    {
        if ($exception !== null) {
            $this->logger->info('Authentication exception', [
                'message' => $exception->getMessageKey(),
                'data' => $exception->getMessageData(),
            ]);
        }

        $data = [
            'message' => 'Unauthorized',
            'code' => JsonResponse::HTTP_UNAUTHORIZED,
            'subCode' => 0,
        ];

        return new JsonResponse($data, JsonResponse::HTTP_UNAUTHORIZED);
    }
}
