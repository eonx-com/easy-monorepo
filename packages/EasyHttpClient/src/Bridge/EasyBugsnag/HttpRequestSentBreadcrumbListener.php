<?php

declare(strict_types=1);

namespace EonX\EasyHttpClient\Bridge\EasyBugsnag;

use Bugsnag\Breadcrumbs\Breadcrumb;
use Bugsnag\Client;
use Carbon\Carbon;
use EonX\EasyHttpClient\Events\HttpRequestSentEvent;
use EonX\EasyHttpClient\Interfaces\EasyHttpClientConstantsInterface;

final class HttpRequestSentBreadcrumbListener
{
    /**
     * @var \Bugsnag\Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function __invoke(HttpRequestSentEvent $event): void
    {
        $this->handle($event);
    }

    public function handle(HttpRequestSentEvent $event): void
    {
        $request = $event->getRequestData();
        $response = $event->getResponseData();

        $this->client->leaveBreadcrumb('HTTP Request Sent', Breadcrumb::REQUEST_TYPE, [
            'Request' => \sprintf('%s - %s', $request->getMethod(), $request->getUrl()),
            'Request Options' => \json_encode($request->getOptions()),
            'Response Status Code' => $response->getStatusCode(),
            'Response Content' => $response->getContent(),
            'Response Headers' => \json_encode($response->getHeaders()),
            'Timing' => $this->getTimingMessage($request->getSentAt(), $response->getReceivedAt()),
        ]);
    }

    private function getCarbon(\DateTimeInterface $dateTime): Carbon
    {
        return Carbon::createFromFormat(
            EasyHttpClientConstantsInterface::DATE_TIME_FORMAT,
            $dateTime->format(EasyHttpClientConstantsInterface::DATE_TIME_FORMAT),
            $dateTime->getTimezone()
        );
    }

    private function getTimingMessage(\DateTimeInterface $sentAt, \DateTimeInterface $receivedAt): string
    {
        $sentAt = $this->getCarbon($sentAt);
        $receivedAt = $this->getCarbon($receivedAt);

        return \sprintf(
            '%s - %s | %s',
            $sentAt->format(EasyHttpClientConstantsInterface::DATE_TIME_FORMAT),
            $receivedAt->format(EasyHttpClientConstantsInterface::DATE_TIME_FORMAT),
            $receivedAt->diffForHumans($sentAt, null, true)
        );
    }
}
