<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Messenger\Exception;

use Symfony\Component\Messenger\Exception\RuntimeException;
use Symfony\Component\Messenger\Exception\UnrecoverableExceptionInterface;

final class UnrecoverableWebhookMessageException extends RuntimeException implements UnrecoverableExceptionInterface
{
    // No body needed
}
