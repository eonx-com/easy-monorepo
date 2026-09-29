<?php
declare(strict_types=1);

namespace EonX\EasyTest\Tests\Unit\Mailer\Trait;

use EonX\EasyTest\Mailer\Trait\QueuedEmailAssertionTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final class QueuedEmailAssertionTraitTest extends TestCase
{
    use QueuedEmailAssertionTrait;

    /**
     * @var \Symfony\Component\Mailer\Event\MessageEvent[]
     */
    private static array $mailerEvents = [];

    public static function getMailerEvents(): array
    {
        return self::$mailerEvents;
    }

    /**
     * @see testItFailsWhenNoEmailMatches
     */
    public static function provideNoMatchData(): iterable
    {
        yield 'Wrong recipient' => [
            'recipient' => 'someone-else@example.com',
            'subject' => 'Some subject',
        ];

        yield 'Wrong subject' => [
            'recipient' => 'recipient@example.com',
            'subject' => 'Some other subject',
        ];

        yield 'Wrong from' => [
            'recipient' => 'recipient@example.com',
            'subject' => 'Some subject',
            'from' => 'someone-else@example.com',
        ];

        yield 'Wrong attachment count' => [
            'recipient' => 'recipient@example.com',
            'subject' => 'Some subject',
            'from' => null,
            'attachmentCount' => 2,
        ];

        yield 'Body does not contain text' => [
            'recipient' => 'recipient@example.com',
            'subject' => 'Some subject',
            'from' => null,
            'attachmentCount' => 1,
            'bodyContains' => ['Goodbye'],
        ];
    }

    #[DataProvider('provideNoMatchData')]
    public function testItFailsWhenNoEmailMatches(
        string $recipient,
        string $subject,
        ?string $from = null,
        int $attachmentCount = 1,
        ?array $bodyContains = null,
    ): void {
        self::$mailerEvents = [self::createMessageEvent(self::createEmail())];

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage(\sprintf(
            'Expected one queued email to "%s" with subject "%s"',
            $recipient,
            $subject
        ));

        self::assertQueuedEmail(
            recipient: $recipient,
            subject: $subject,
            from: $from,
            attachmentCount: $attachmentCount,
            bodyContains: $bodyContains
        );
    }

    public function testItFailsWhenSeveralEmailsMatch(): void
    {
        self::$mailerEvents = [
            self::createMessageEvent(self::createEmail()),
            self::createMessageEvent(self::createEmail()),
        ];

        $this->expectException(ExpectationFailedException::class);

        self::assertQueuedEmail(
            recipient: 'recipient@example.com',
            subject: 'Some subject',
            attachmentCount: 1
        );
    }

    public function testItReturnsMatchingEmail(): void
    {
        $expectedEmail = self::createEmail();
        self::$mailerEvents = [
            self::createMessageEvent(self::createEmail(), false),
            self::createMessageEvent(self::createEmail()->subject('Some other subject')),
            self::createMessageEvent($expectedEmail),
        ];

        $email = self::assertQueuedEmail(
            recipient: 'recipient@example.com',
            subject: 'Some subject',
            from: 'sender@example.com',
            attachmentCount: 1,
            bodyContains: ['Hello', 'world']
        );

        self::assertSame($expectedEmail, $email);
    }

    private static function createEmail(): Email
    {
        return new Email()
            ->from('sender@example.com')
            ->to('someone@example.com', 'recipient@example.com')
            ->subject('Some subject')
            ->html('<p>Hello world</p>')
            ->attach('some-content', 'some-file.txt');
    }

    private static function createMessageEvent(Email $email, bool $queued = true): MessageEvent
    {
        return new MessageEvent(
            $email,
            new Envelope(new Address('sender@example.com'), [new Address('recipient@example.com')]),
            'default',
            $queued
        );
    }
}
