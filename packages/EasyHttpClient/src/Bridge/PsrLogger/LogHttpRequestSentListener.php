<?php

declare(strict_types=1);

namespace EonX\EasyHttpClient\Bridge\PsrLogger;

use EonX\EasyHttpClient\Events\HttpRequestSentEvent;
use EonX\EasyHttpClient\Interfaces\EasyHttpClientConstantsInterface;
use Psr\Log\LoggerInterface;

final class LogHttpRequestSentListener
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(HttpRequestSentEvent $event): void
    {
        $this->handle($event);
    }

    public function handle(HttpRequestSentEvent $event): void
    {
        $request = $event->getRequestData();
        $response = $event->getResponseData();

        $requestMessage = \sprintf('Request: "%s %s"', $request->getMethod(), $request->getUrl());
        $requestContext = [
            'http_options' => $request->getOptions(),
            'sent_at' => $request->getSentAt()
                ->format(EasyHttpClientConstantsInterface::DATE_TIME_FORMAT),
        ];

        $responseMessage = \sprintf('Response: "%d %s"', $response->getStatusCode(), $request->getUrl());
        $responseContext = [
            'content' => $response->getContent(),
            'headers' => $response->getHeaders(),
            'received_at' => $response->getReceivedAt()
                ->format(EasyHttpClientConstantsInterface::DATE_TIME_FORMAT),
        ];

        $this->logger->debug($requestMessage, $requestContext);
        $this->logger->debug($responseMessage, $responseContext);
    }
}
