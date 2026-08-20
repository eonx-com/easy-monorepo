<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Factory;

use EonX\EasyWebhook\Common\Exception\InvalidSsrfProtectionConfigException;
use EonX\EasyWebhook\Common\HttpClient\RequestLimitsHttpClient;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\NoPrivateNetworkHttpClient;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class HttpClientFactory implements HttpClientFactoryInterface
{
    public const DEFAULT_MAX_DURATION = 30;

    public const DEFAULT_MAX_RESPONSE_BYTES = 1024 * 1024;

    public const DEFAULT_TIMEOUT = 10;

    /**
     * @param string[] $extraBlockedRanges
     * @param string[] $allowedRanges
     */
    public function __construct(
        private bool $blockPrivateNetworks = true,
        private array $extraBlockedRanges = [],
        private array $allowedRanges = [],
        private bool $requestLimitsEnabled = false,
        private int $timeout = self::DEFAULT_TIMEOUT,
        private int $maxDuration = self::DEFAULT_MAX_DURATION,
        private int $maxResponseBytes = self::DEFAULT_MAX_RESPONSE_BYTES,
    ) {
        // Only when protection is on; disabled means allowed_ranges is inert, so don't reject it
        if ($blockPrivateNetworks) {
            self::validateAllowedRanges($extraBlockedRanges, $allowedRanges);
        }
    }

    /**
     * Rejects an "allowed_ranges" entry that would silently do nothing, so a broken config fails at
     * startup instead of leaving a host the operator believes is reachable still blocked. Called
     * from the Symfony config validator (fails at container build) and from the constructor
     * (Laravel / direct instantiation).
     *
     * @param string[] $extraBlockedRanges
     * @param string[] $allowedRanges
     */
    public static function validateAllowedRanges(array $extraBlockedRanges, array $allowedRanges): void
    {
        if ($allowedRanges === []) {
            return;
        }

        $blockSource = [...IpUtils::PRIVATE_SUBNETS, ...$extraBlockedRanges];
        $subnets = \array_values(\array_diff($blockSource, $allowedRanges));

        foreach ($allowedRanges as $range) {
            if (\in_array($range, $blockSource, true) === false) {
                throw new InvalidSsrfProtectionConfigException(\sprintf(
                    'SSRF "allowed_ranges" entry "%s" does not match any blocked range, so it has no '
                    . 'effect. It must be one of the default private/reserved ranges (or an '
                    . '"extra_blocked_ranges" entry) written exactly as listed.',
                    $range
                ));
            }

            // The allowed_ranges option removes a matching entry from the block list, so it can
            // only reach an address that entry is the sole cover for. If a broader default still
            // covers it (e.g. "::1/128" sits inside "::/96"), removing the exact string leaves the
            // address blocked; removing the broader range instead would be far too permissive
            if (IpUtils::checkIp(\explode('/', $range)[0], $subnets)) {
                throw new InvalidSsrfProtectionConfigException(\sprintf(
                    'SSRF "allowed_ranges" entry "%s" cannot be carved out: a broader default range '
                    . 'still covers it (for example "::1/128" is covered by "::/96"). This is not '
                    . 'supported; use "ssrf_protection.enabled: false" to reach such hosts.',
                    $range
                ));
            }
        }
    }

    public function create(): HttpClientInterface
    {
        $httpClient = HttpClient::create([
            'http_version' => '1.1',
        ]);

        // SSRF guard (enabled by default): reject any request whose resolved peer IP falls in a
        // blocked range, re-checked on every redirect hop. This defeats both DNS-rebinding (the
        // resolved IP is inspected, not the hostname) and redirect-to-internal, neither of which a
        // URL-string check can catch. Range selection:
        // - both empty -> null, i.e. the full standard private + reserved defaults (incl. link-local
        // 169.254.0.0/16 used by cloud metadata endpoints); null gets the widest coverage
        // - otherwise -> the defaults PLUS extraBlockedRanges, MINUS allowedRanges. Building on the
        // defaults keeps IPv6 entries in the list, which NoPrivateNetworkHttpClient needs to check
        // IPv6 peers at all; validateAllowedRanges() has already rejected a no-op allowedRanges entry
        if ($this->blockPrivateNetworks) {
            $subnets = $this->extraBlockedRanges === [] && $this->allowedRanges === []
                ? null
                : \array_values(\array_diff(
                    [...IpUtils::PRIVATE_SUBNETS, ...$this->extraBlockedRanges],
                    $this->allowedRanges
                ));

            $httpClient = new NoPrivateNetworkHttpClient($httpClient, $subnets);
        }

        // DoS request limits (opt-in): enforced inside the decorator, not as client-default options,
        // so a per-webhook http client option cannot silently raise or disable them.
        // @todo Change the default to enabled (on) in 7.x
        if ($this->requestLimitsEnabled) {
            $httpClient = new RequestLimitsHttpClient(
                $httpClient,
                $this->timeout,
                $this->maxDuration,
                $this->maxResponseBytes,
            );
        }

        return $httpClient;
    }
}
