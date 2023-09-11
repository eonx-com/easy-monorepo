<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Exceptions;

use EonX\EasyWebhook\Interfaces\DoNotHandleMeEasyWebhookExceptionInterface as DoNotHandleMeInterface;

abstract class AbstractDoNotHandleMeException extends AbstractEasyWebhookException implements DoNotHandleMeInterface
{
    // No need for body
}
