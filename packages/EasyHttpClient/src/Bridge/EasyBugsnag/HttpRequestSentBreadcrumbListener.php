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
        $receivedAt = null;
        $request = $event->getRequestData();
        $response = $event->getResponseData();
        $throwable = $event->getThrowable();

        $metaData = [
            'Request' => \sprintf('%s - %s', $request->getMethod(), $request->getUrl()),
            'Request Options' => \json_encode($request->getOptions()),
        ];

        if ($response !== null) {
            $metaData['Response Status Code'] = $response->getStatusCode();
            $metaData['Response Content'] = $response->getContent();
            $metaData['Response Headers'] = \json_encode($response->getHeaders());

            $receivedAt = $response->getReceivedAt();
        }

        if ($throwable !== null) {
            $metaData['Throwable Class'] = \get_class($throwable);
            $metaData['Throwable Message'] = $throwable->getMessage();

            $receivedAt = $event->getThrowableThrownAt();
        }

        $metaData['Timing'] = $this->getTimingMessage($request->getSentAt(), $receivedAt);

        $this->client->leaveBreadcrumb('HTTP Request Sent', Breadcrumb::REQUEST_TYPE, $metaData);
    }

    private function getCarbon(\DateTimeInterface $dateTime): Carbon
    {
        if ($dateTime instanceof Carbon) {
            return $dateTime;
        }

        return Carbon::createFromFormat(
            EasyHttpClientConstantsInterface::DATE_TIME_FORMAT,
            $dateTime->format(EasyHttpClientConstantsInterface::DATE_TIME_FORMAT),
            $dateTime->getTimezone()
        );
    }

    private function getTimingMessage(\DateTimeInterface $sentAt, ?\DateTimeInterface $receivedAt = null): string
    {
        if ($receivedAt === null) {
            return 'No timing available';
        }

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
