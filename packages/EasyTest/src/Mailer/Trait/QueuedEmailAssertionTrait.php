<?php
declare(strict_types=1);

namespace EonX\EasyTest\Mailer\Trait;

use PHPUnit\Framework\Constraint\Constraint;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Test\Constraint\EmailAttachmentCount;
use Symfony\Component\Mime\Test\Constraint\EmailHeaderSame;
use Symfony\Component\Mime\Test\Constraint\EmailHtmlBodyContains;

/**
 * @mixin \PHPUnit\Framework\TestCase
 * @mixin \Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait
 */
trait QueuedEmailAssertionTrait
{
    /**
     * @param string[]|null $bodyContains
     */
    protected static function assertQueuedEmail(
        string $recipient,
        string $subject,
        ?string $from = null,
        int $attachmentCount = 0,
        ?array $bodyContains = null,
    ): Email {
        $matched = \array_values(\array_filter(
            self::getMailerEvents(),
            static fn(MessageEvent $event): bool => self::isQueuedEmailMatching(
                event: $event,
                recipient: $recipient,
                subject: $subject,
                from: $from,
                attachmentCount: $attachmentCount,
                bodyContains: $bodyContains
            )
        ));

        self::assertCount(1, $matched, \sprintf(
            'Expected one queued email to "%s" with subject "%s", from "%s", %d attachment(s) and a body containing'
            . ' [%s]',
            $recipient,
            $subject,
            $from ?? '*',
            $attachmentCount,
            \implode(', ', $bodyContains ?? [])
        ));

        /** @var \Symfony\Component\Mime\Email $email */
        $email = $matched[0]->getMessage();

        return $email;
    }

    /**
     * @param string[]|null $bodyContains
     */
    private static function isQueuedEmailMatching(
        MessageEvent $event,
        string $recipient,
        string $subject,
        ?string $from,
        int $attachmentCount,
        ?array $bodyContains,
    ): bool {
        $email = $event->getMessage();

        if ($event->isQueued() === false || $email instanceof Email === false) {
            return false;
        }

        if ((string)$email->getSubject() !== $subject) {
            return false;
        }

        $isForRecipient = \array_any(
            $email->getTo(),
            static fn(Address $address): bool => $address->getAddress() === $recipient
        );

        if ($isForRecipient === false) {
            return false;
        }

        $constraints = [new EmailAttachmentCount($attachmentCount)];

        if ($from !== null) {
            $constraints[] = new EmailHeaderSame('from', $from);
        }

        foreach ($bodyContains ?? [] as $text) {
            $constraints[] = new EmailHtmlBodyContains($text);
        }

        return \array_all(
            $constraints,
            static fn(Constraint $constraint): bool => $constraint->evaluate($email, '', true) === true
        );
    }
}
