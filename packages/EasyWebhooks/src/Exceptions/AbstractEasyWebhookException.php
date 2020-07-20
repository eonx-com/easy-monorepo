<?php

declare(strict_types=1);

namespace EonX\EasyWebhooks\Exceptions;

use EonX\EasyWebhooks\Interfaces\EasyWebhookExceptionInterface;

abstract class AbstractEasyWebhookException extends \RuntimeException implements EasyWebhookExceptionInterface
{
    // No body needed.
}
