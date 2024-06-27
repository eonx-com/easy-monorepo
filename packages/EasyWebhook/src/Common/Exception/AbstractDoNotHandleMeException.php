<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Exception;

use EonX\EasyWebhook\Common\Exception\DoNotHandleMeEasyWebhookExceptionInterface as DoNotHandleMeInterface;

abstract class AbstractDoNotHandleMeException extends AbstractEasyWebhookException implements DoNotHandleMeInterface
{
    // No need for body
}
