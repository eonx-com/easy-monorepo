<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Common\Listener;

use EonX\EasyHttpClient\Common\Event\HttpRequestSentEvent;

final class AwsMessagingRequestSanitizerListener
{
    private const BODY_KEYS_TO_REDACT = [
        'BccAddresses',
        'CcAddresses',
        'Data',
        'Message',
        'PhoneNumber',
        'RawContent',
        'ReplyToAddresses',
        'TemplateData',
        'ToAddresses',
    ];

    private const HEADERS_TO_REDACT = [
        'authorization',
        'x-amz-security-token',
    ];

    private const HOST_PATTERN = '/^(email|sns)\.[a-z0-9-]+\.amazonaws\.com$/';

    private const REDACTED = '*REDACTED*';

    public function __invoke(HttpRequestSentEvent $event): void
    {
        $requestData = $event->getRequestData();
        $options = $requestData->getOptions();
        $host = \parse_url($requestData->getUrl(), \PHP_URL_HOST)
            ?? \parse_url((string)($options['base_uri'] ?? ''), \PHP_URL_HOST);

        if (\preg_match(self::HOST_PATTERN, \strtolower((string)$host)) !== 1) {
            return;
        }

        if (\array_key_exists('body', $options)) {
            $options['body'] = self::redactBody($options['body']);
        }

        if (\is_array($options['json'] ?? null)) {
            $options['json'] = self::redactArray($options['json']);
        }

        if (\is_array($options['headers'] ?? null)) {
            $options['headers'] = self::redactHeaders($options['headers']);
        }

        $requestData->setOptions($options);
    }

    private static function redactArray(array $data): array
    {
        foreach ($data as $key => $value) {
            if (\in_array($key, self::BODY_KEYS_TO_REDACT, true)) {
                $data[$key] = self::REDACTED;

                continue;
            }

            if (\is_array($value)) {
                $data[$key] = self::redactArray($value);
            }
        }

        return $data;
    }

    private static function redactBody(mixed $body): array|string
    {
        if (\is_array($body)) {
            return self::redactArray($body);
        }

        if (\is_string($body) === false) {
            return self::REDACTED;
        }

        $data = \json_decode($body, true);

        if (\is_array($data)) {
            return (string)\json_encode(self::redactArray($data));
        }

        $formKeys = \implode('|', self::BODY_KEYS_TO_REDACT);

        return (string)\preg_replace('/(?<=^|&)(' . $formKeys . ')=[^&]*/', '$1=' . self::REDACTED, $body);
    }

    private static function redactHeaders(array $headers): array
    {
        foreach ($headers as $name => $value) {
            if (\is_int($name) && \is_string($value)) {
                $headerName = \trim(\explode(':', $value, 2)[0]);

                if (\in_array(\strtolower($headerName), self::HEADERS_TO_REDACT, true)) {
                    $headers[$name] = $headerName . ': ' . self::REDACTED;
                }

                continue;
            }

            if (\in_array(\strtolower((string)$name), self::HEADERS_TO_REDACT, true)) {
                $headers[$name] = \is_array($value) ? \array_fill(0, \count($value), self::REDACTED) : self::REDACTED;
            }
        }

        return $headers;
    }
}
