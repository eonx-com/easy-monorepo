<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Exceptions;

use EonX\EasyWebhook\Interfaces\EasyWebhookExceptionInterface;
use RuntimeException;

abstract class AbstractEasyWebhookException extends RuntimeException implements EasyWebhookExceptionInterface
{
    // No body needed
}
