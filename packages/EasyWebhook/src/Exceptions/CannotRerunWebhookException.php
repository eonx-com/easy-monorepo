<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Exceptions;

use Symfony\Component\Messenger\Exception\UnrecoverableExceptionInterface;

final class CannotRerunWebhookException extends AbstractDoNotHandleMeException implements
    UnrecoverableExceptionInterface
{
    // No body needed.
}
