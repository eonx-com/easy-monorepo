<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Exception;

use RuntimeException;

abstract class AbstractEasyWebhookException extends RuntimeException implements EasyWebhookExceptionInterface
{
    // No body needed
}
