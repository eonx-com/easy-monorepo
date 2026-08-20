<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Exception;

/**
 * Thrown when the SSRF-protection configuration is invalid — for example an "allowed_ranges" entry
 * that would not actually unblock anything (it is not a default range, or it stays covered by a
 * broader default).
 */
final class InvalidSsrfProtectionConfigException extends AbstractEasyWebhookException
{
    // No body needed
}
