<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Common\Listener;

use EonX\EasyHttpClient\Common\Event\HttpRequestSentEvent;

final class AwsMessagingRequestSanitizerListener
{
    private const array BODY_KEYS_TO_REDACT = [
        'BccAddresses',
        'CcAddresses',
        'CustomUserData',
        'Data',
        'Destinations',
        'EmailAddress',
        'Endpoint',
        'Message',
        'OneTimePassword',
        'PhoneNumber',
        'phoneNumber',
        'RawContent',
        'ReplacementTemplateData',
        'ReplyToAddresses',
        'Subject',
        'TemplateData',
        'ToAddresses',
        'Token',
    ];

    private const string FORM_BODY_PATTERN = '/^[A-Za-z0-9._~%*+-]+=[^&\s]*(?:&[A-Za-z0-9._~%*+-]+=[^&\s]*)*$/';

    private const array HEADERS_TO_REDACT = [
        'authorization',
        'x-amz-security-token',
    ];

    private const string HOST_PATTERN = '/^(email|sns)(-fips)?\.[a-z0-9-]+\.amazonaws\.com(\.cn)?$/';

    private const int JSON_ENCODE_FLAGS = \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE;

    private const string REDACTED = '*REDACTED*';

    private const array RESPONSE_KEYS_TO_REDACT = [
        'Message',
        'message',
    ];

    public function __invoke(HttpRequestSentEvent $event): void
    {
        $requestData = $event->getRequestData();
        $options = $requestData->getOptions();
        $host = \parse_url($requestData->getUrl(), \PHP_URL_HOST)
            ?? \parse_url((string) ($options['base_uri'] ?? ''), \PHP_URL_HOST);

        if (\preg_match(self::HOST_PATTERN, \strtolower((string) $host)) !== 1) {
            return;
        }

        if (isset($options['body'])) {
            $options['body'] = self::redactBody($options['body']);
        }

        if (isset($options['json'])) {
            $options['json'] = \is_array($options['json']) ? self::redactArray($options['json']) : self::REDACTED;
        }

        if (\is_array($options['headers'] ?? null)) {
            $options['headers'] = self::redactHeaders($options['headers']);
        }

        $requestData->setOptions($options);

        $responseData = $event->getResponseData();
        $responseData?->setContent(self::redactResponseContent($responseData->getContent()));
    }

    private static function isKeyToRedact(string $key): bool
    {
        return \array_intersect(\explode('.', $key), self::BODY_KEYS_TO_REDACT) !== [];
    }

    private static function redactArray(array $data): array
    {
        foreach ($data as $key => $value) {
            if (self::isKeyToRedact((string) $key)) {
                $data[$key] = self::REDACTED;

                continue;
            }

            if (\is_array($value)) {
                $data[$key] = self::redactArray($value);

                continue;
            }

            if (\is_object($value)) {
                $data[$key] = self::REDACTED;
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

        if ($body === '') {
            return $body;
        }

        $data = \json_decode($body, true);

        if (\is_array($data)) {
            $redactedData = self::redactArray($data);

            return $redactedData === $data
                ? $body
                : (string) \json_encode($redactedData, self::JSON_ENCODE_FLAGS);
        }

        if (\preg_match(self::FORM_BODY_PATTERN, $body) !== 1) {
            return self::REDACTED;
        }

        return (string) \preg_replace_callback(
            '/(^|&)([^=&]+)=[^&]*/',
            static fn(array $matches): string => self::isKeyToRedact(\urldecode($matches[2]))
                ? $matches[1] . $matches[2] . '=' . self::REDACTED
                : $matches[0],
            $body
        );
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

            if (\in_array(\strtolower((string) $name), self::HEADERS_TO_REDACT, true)) {
                $headers[$name] = \is_array($value) ? \array_fill(0, \count($value), self::REDACTED) : self::REDACTED;
            }
        }

        return $headers;
    }

    private static function redactResponseContent(string $content): string
    {
        if ($content === '') {
            return $content;
        }

        $data = \json_decode($content, true);

        if (\is_array($data)) {
            $redactedData = $data;

            foreach (self::RESPONSE_KEYS_TO_REDACT as $key) {
                if (\array_key_exists($key, $redactedData)) {
                    $redactedData[$key] = self::REDACTED;
                }
            }

            return $redactedData === $data ? $content : (string) \json_encode($redactedData, self::JSON_ENCODE_FLAGS);
        }

        if (\str_starts_with(\ltrim($content), '<') === false) {
            return self::REDACTED;
        }

        return \preg_replace(
            '/<Message>.*?(?:<\/Message>|\z)/s',
            '<Message>' . self::REDACTED . '</Message>',
            $content
        ) ?? self::REDACTED;
    }
}
