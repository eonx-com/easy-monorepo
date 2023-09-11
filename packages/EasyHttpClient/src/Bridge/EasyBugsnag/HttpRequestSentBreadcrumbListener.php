<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Bridge\EasyBugsnag;

use Bugsnag\Breadcrumbs\Breadcrumb;
use Bugsnag\Client;
use Carbon\Carbon;
use DateTimeInterface;
use EonX\EasyHttpClient\Events\HttpRequestSentEvent;
use EonX\EasyHttpClient\Interfaces\EasyHttpClientConstantsInterface;

final class HttpRequestSentBreadcrumbListener
{
    public const BREADCRUMB_NAME = 'HTTP Request Sent';

    public const DEFAULT_TIMING_MESSAGE = 'No timing available';

    // The metadata attributes priority list (low to high)
    private const METADATA_ATTRIBUTES_PRIORITY_LIST = [
        self::METADATA_ATTRIBUTE_RESPONSE_HEADERS,
        self::METADATA_ATTRIBUTE_REQUEST_OPTIONS,
        self::METADATA_ATTRIBUTE_RESPONSE_CONTENT,
        self::METADATA_ATTRIBUTE_THROWABLE_CLASS,
        self::METADATA_ATTRIBUTE_THROWABLE_MESSAGE,
        self::METADATA_ATTRIBUTE_TIMING,
        self::METADATA_ATTRIBUTE_RESPONSE_STATUS_CODE,
        self::METADATA_ATTRIBUTE_REQUEST,
    ];

    private const METADATA_ATTRIBUTE_REQUEST = 'Request';

    private const METADATA_ATTRIBUTE_REQUEST_OPTIONS = 'Request Options';

    private const METADATA_ATTRIBUTE_RESPONSE_CONTENT = 'Response Content';

    private const METADATA_ATTRIBUTE_RESPONSE_HEADERS = 'Response Headers';

    private const METADATA_ATTRIBUTE_RESPONSE_STATUS_CODE = 'Response Status Code';

    private const METADATA_ATTRIBUTE_THROWABLE_CLASS = 'Throwable Class';

    private const METADATA_ATTRIBUTE_THROWABLE_MESSAGE = 'Throwable Message';

    private const METADATA_ATTRIBUTE_TIMING = 'Timing';

    public function __construct(
        private Client $client,
    ) {
        // The body is not required
    }

    public function __invoke(HttpRequestSentEvent $event): void
    {
        $receivedAt = null;
        $request = $event->getRequestData();
        $response = $event->getResponseData();
        $throwable = $event->getThrowable();

        $metadata = [
            self::METADATA_ATTRIBUTE_REQUEST => \sprintf('%s - %s', $request->getMethod(), $request->getUrl()),
            self::METADATA_ATTRIBUTE_REQUEST_OPTIONS => \json_encode($request->getOptions()),
        ];

        if ($response !== null) {
            $metadata[self::METADATA_ATTRIBUTE_RESPONSE_STATUS_CODE] = $response->getStatusCode();
            $metadata[self::METADATA_ATTRIBUTE_RESPONSE_CONTENT] = $response->getContent();
            $metadata[self::METADATA_ATTRIBUTE_RESPONSE_HEADERS] = \json_encode($response->getHeaders());

            $receivedAt = $response->getReceivedAt();
        }

        if ($throwable !== null) {
            $metadata[self::METADATA_ATTRIBUTE_THROWABLE_CLASS] = $throwable::class;
            $metadata[self::METADATA_ATTRIBUTE_THROWABLE_MESSAGE] = $throwable->getMessage();

            $receivedAt = $event->getThrowableThrownAt();
        }

        $metadata[self::METADATA_ATTRIBUTE_TIMING] = $this->getTimingMessage($request->getSentAt(), $receivedAt);

        $this->client->leaveBreadcrumb(
            self::BREADCRUMB_NAME,
            Breadcrumb::REQUEST_TYPE,
            $this->prepareMetadata($metadata)
        );
    }

    private function calculateBreadcrumbSize(array $metadata): int
    {
        $breadcrumb = new Breadcrumb(self::BREADCRUMB_NAME, Breadcrumb::REQUEST_TYPE, $metadata);
        $breadcrumbData = $breadcrumb->toArray();
        $breadcrumbData['metaData'] = $metadata;

        return \strlen((string)\json_encode($breadcrumbData));
    }

    private function getCarbonInstance(DateTimeInterface $dateTime): Carbon
    {
        if ($dateTime instanceof Carbon) {
            return $dateTime;
        }

        return Carbon::instance($dateTime);
    }

    private function getTimingMessage(DateTimeInterface $sentAt, ?DateTimeInterface $receivedAt = null): string
    {
        if ($receivedAt === null) {
            return self::DEFAULT_TIMING_MESSAGE;
        }

        $sentAt = $this->getCarbonInstance($sentAt);
        $receivedAt = $this->getCarbonInstance($receivedAt);

        return \sprintf(
            '%s - %s | %s',
            $sentAt->format(EasyHttpClientConstantsInterface::DATE_TIME_FORMAT),
            $receivedAt->format(EasyHttpClientConstantsInterface::DATE_TIME_FORMAT),
            $receivedAt->diffForHumans($sentAt, null, true)
        );
    }

    private function prepareMetadata(array $metadata): array
    {
        $metadataAttributes = self::METADATA_ATTRIBUTES_PRIORITY_LIST;

        while (
            $this->calculateBreadcrumbSize($metadata) > Breadcrumb::MAX_SIZE &&
            \count($metadataAttributes) !== 0 &&
            \count($metadata) !== 0
        ) {
            $attributeToRemove = \array_shift($metadataAttributes);

            unset($metadata[$attributeToRemove]);
        }

        return $metadata;
    }
}
