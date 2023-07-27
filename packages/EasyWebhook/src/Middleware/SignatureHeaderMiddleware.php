<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Middleware;

use EonX\EasyWebhook\Exceptions\InvalidWebhookSecretException;
use EonX\EasyWebhook\Interfaces\StackInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use EonX\EasyWebhook\Interfaces\WebhookSignerInterface;

final class SignatureHeaderMiddleware extends AbstractConfigureOnceMiddleware
{
    private string $signatureHeader;

    public function __construct(
        private WebhookSignerInterface $signer,
        private ?string $secret = null,
        ?string $signatureHeader = null,
        ?int $priority = null,
    ) {
        $this->signatureHeader = $signatureHeader ?? WebhookInterface::HEADER_SIGNATURE;

        parent::__construct($priority);
    }

    protected function doProcess(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface
    {
        $options = $webhook->getHttpClientOptions();
        $body = $webhook->getBodyAsString() ?? $options['body'] ?? null;

        if (\is_string($body)) {
            $secret = $webhook->getSecret() ?? $this->secret;

            if ($secret === null || $secret === '') {
                throw new InvalidWebhookSecretException('Secret for signature is required');
            }

            $webhook->header($this->signatureHeader, $this->signer->sign($body, $secret));
        }

        return $stack
            ->next()
            ->process($webhook, $stack);
    }
}
