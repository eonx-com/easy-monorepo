<?php
declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\Listeners;

use ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException;
use EonX\EasyAsync\Events\JobLogFailedEvent;

final class AddValidationErrorsOnJobLogListener
{
    public function __invoke(JobLogFailedEvent $event): void
    {
        $throwable = $event->getThrowable();

        if ($throwable instanceof ValidationException) {
            $errors = [];

            foreach ($throwable->getConstraintViolationList() as $violation) {
                /** @var \Symfony\Component\Validator\ConstraintViolationInterface $violation */
                $errors[$violation->getPropertyPath()] = [
                    'message' => $violation->getMessageTemplate(),
                    'parameters' => $violation->getParameters()
                ];
            }
        }
    }
}
