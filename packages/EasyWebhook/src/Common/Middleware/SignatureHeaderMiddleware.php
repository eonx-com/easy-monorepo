<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Middleware;

use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use EonX\EasyWebhook\Common\Exception\InvalidWebhookSecretException;
use EonX\EasyWebhook\Common\Signer\WebhookSignerInterface;
use EonX\EasyWebhook\Common\Stack\StackInterface;

final class SignatureHeaderMiddleware extends AbstractConfigureOnceMiddleware
{
    private readonly string $signatureHeader;

    public function __construct(
        private readonly WebhookSignerInterface $signer,
        private readonly ?string $secret = null,
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
