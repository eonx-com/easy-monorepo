<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Configurators;

use EonX\EasyWebhook\Exceptions\InvalidWebhookSecretException;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookSignerInterface;

final class SignatureWebhookConfigurator extends AbstractWebhookConfigurator
{
    /**
     * @var null|string
     */
    private $secret;

    /**
     * @var string
     */
    private $signatureHeader;

    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookSignerInterface
     */
    private $signer;

    public function __construct(
        WebhookSignerInterface $signer,
        ?string $secret = null,
        ?string $signatureHeader = null,
        ?int $priority = null
    ) {
        $this->signer = $signer;
        $this->secret = $secret;
        $this->signatureHeader = $signatureHeader ?? 'X-Signature';

        parent::__construct($priority);
    }

    public function configure(WebhookInterface $webhook): void
    {
        $options = $webhook->getHttpClientOptions();

        if (\is_string($options['body'] ?? null) === false) {
            return;
        }

        $secret = $webhook->getSecret() ?? $this->secret;

        if (empty($secret)) {
            throw new InvalidWebhookSecretException('Secret for signature is required');
        }

        $webhook->mergeHttpClientOptions([
            'headers' => [
                $this->signatureHeader => $this->signer->sign($options['body'], $secret),
            ],
        ]);
    }
}
