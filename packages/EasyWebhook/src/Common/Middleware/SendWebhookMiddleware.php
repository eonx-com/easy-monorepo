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
    private const array CONNECTION_OPTIONS = ['bindto', 'proxy', 'resolve'];

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

    /**
     * The options are stored with the webhook, so they are data. These ones are instructions: they write a file,
     * call code or change where the request connects
     */
    private function assertOptionsAllowed(array $options): void
    {
        $notAllowedOptions = [];
        $extra = $options['extra'] ?? null;
        $onProgress = $options['on_progress'] ?? null;

        if (\is_array($extra) && (bool)($extra['curl'] ?? false)) {
            $notAllowedOptions[] = 'extra.curl';
        }

        if (\is_string($onProgress)
            || (\is_array($onProgress) && $onProgress !== [] && \array_filter($onProgress, \is_object(...)) === [])
        ) {
            $notAllowedOptions[] = 'on_progress';
        }

        if ($this->ssrfProtectionEnabled) {
            foreach (self::CONNECTION_OPTIONS as $option) {
                $value = $options[$option] ?? null;

                // The HTTP client uses any proxy that is not null ("0" and "" too), the other options when not empty
                if ($option === 'proxy' ? $value !== null : (bool)$value) {
                    $notAllowedOptions[] = $option;
                }
            }
        }

        if ($notAllowedOptions !== []) {
            throw new WebhookHttpClientOptionNotAllowedException(\sprintf(
                'The "%s" HTTP client option is not allowed for a webhook.',
                $notAllowedOptions[0]
            ));
        }
    }
}
