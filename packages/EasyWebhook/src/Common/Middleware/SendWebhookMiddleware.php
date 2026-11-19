<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Middleware;

use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Entity\WebhookResult;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use EonX\EasyWebhook\Common\Exception\InvalidWebhookUrlException;
use EonX\EasyWebhook\Common\Exception\WebhookHttpClientOptionNotAllowedException;
use EonX\EasyWebhook\Common\Exception\WebhookRequestFailedException;
use EonX\EasyWebhook\Common\Stack\StackInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

final class SendWebhookMiddleware extends AbstractMiddleware
{
    private const array ALLOWED_EXTRA_OPTIONS = ['no_cache', 'trace_content', 'use_persistent_connections'];

    private const array ALLOWED_OPTIONS = [
        'auth_basic',
        'auth_bearer',
        'body',
        'buffer',
        'capture_peer_cert_chain',
        'headers',
        'http_version',
        'json',
        'max_connect_duration',
        'max_duration',
        'max_redirects',
        'query',
        'timeout',
        'user_data',
    ];

    private const array CONNECTION_OPTIONS = ['no_proxy', 'proxy', 'resolve'];

    private const array TLS_VERIFY_OPTIONS = ['verify_host', 'verify_peer'];

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        ?int $priority = null,
        private readonly bool $ssrfProtectionEnabled = true,
    ) {
        parent::__construct($priority);
    }

    public function process(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface
    {
        $method = $webhook->getMethod() ?? WebhookInterface::DEFAULT_METHOD;
        $url = $webhook->getUrl() ?? '';

        if ($url === '') {
            throw new InvalidWebhookUrlException('Webhook URL required');
        }

        $throwable = null;

        try {
            $options = $webhook->getHttpClientOptions() ?? [];
            $this->assertOptionsAllowed($options);

            $response = $this->httpClient->request($method, $url, $options);
            // Trigger exception on bad response
            $response->getContent();
        } catch (Throwable $throwable) {
            // Set response to null here to make sure not to carry over the faulty response
            $response = null;

            if ($throwable instanceof HttpExceptionInterface) {
                $response = $throwable->getResponse();
            }

            if ($throwable instanceof ExceptionInterface) {
                $throwable = new WebhookRequestFailedException(
                    message: 'Webhook request failed: ' . $throwable->getMessage(),
                    previous: $throwable
                );
            }
        }

        return new WebhookResult($webhook, $response, $throwable);
    }

    private function assertOptionsAllowed(array $options): void
    {
        $notAllowedOptions = [];

        foreach ($options as $name => $value) {
            $name = (string)$name;

            if ($name === 'extra') {
                $notAllowedOptions = [...$notAllowedOptions, ...$this->findNotAllowedExtraOptions($value)];

                continue;
            }

            if ($this->isOptionAllowed($name, $value) === false) {
                $notAllowedOptions[] = $name;
            }
        }

        // All of them at once: the webhook fails for good, so the author has to fix every option in one go
        if ($notAllowedOptions !== []) {
            throw new WebhookHttpClientOptionNotAllowedException(\sprintf(
                'HTTP client options not allowed for a webhook: "%s".',
                \implode('", "', $notAllowedOptions)
            ));
        }
    }

    /**
     * @return string[]
     */
    private function findNotAllowedExtraOptions(mixed $extra): array
    {
        if (\is_array($extra) === false) {
            return ['extra'];
        }

        $notAllowedOptions = [];

        foreach (\array_keys($extra) as $name) {
            if (\in_array($name, self::ALLOWED_EXTRA_OPTIONS, true) === false) {
                $notAllowedOptions[] = 'extra.' . $name;
            }
        }

        return $notAllowedOptions;
    }

    private function isOnProgressAllowed(mixed $onProgress): bool
    {
        // Only a callable with an object can be trusted: it cannot come from the stored options. A stored closure
        // comes back as an empty array, which the HTTP client would refuse on every attempt
        return $onProgress === null
            || \is_object($onProgress)
            || (\is_array($onProgress) && \array_filter($onProgress, \is_object(...)) !== []);
    }

    private function isOptionAllowed(string $name, mixed $value): bool
    {
        if (\in_array($name, self::ALLOWED_OPTIONS, true)) {
            return true;
        }

        // Any other value (false, null, 0) turns the check off in the HTTP client
        if (\in_array($name, self::TLS_VERIFY_OPTIONS, true)) {
            return $value === true;
        }

        if ($name === 'on_progress') {
            return $this->isOnProgressAllowed($value);
        }

        // An existing file path is a Unix socket for the HTTP client, which no webhook URL can reach, so it is
        // rejected also with the SSRF protection disabled. The local interface belongs on the HTTP client itself
        if ($name === 'bindto') {
            return (bool)$value === false;
        }

        if (\in_array($name, self::CONNECTION_OPTIONS, true)) {
            // The HTTP client uses any proxy that is not null ("0" and "" too). A no_proxy replaces the NO_PROXY
            // environment variable, so it can skip an egress proxy; an empty one only removes the exceptions
            $isEmpty = match ($name) {
                'no_proxy' => $value === null || $value === '',
                'proxy' => $value === null,
                default => (bool)$value === false,
            };

            return $this->ssrfProtectionEnabled === false || $isEmpty;
        }

        return false;
    }
}
