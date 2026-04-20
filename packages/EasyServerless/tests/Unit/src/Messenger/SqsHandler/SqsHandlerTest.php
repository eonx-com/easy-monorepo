<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\src\Messenger\SqsHandler;

use AsyncAws\Sqs\SqsClient as AsyncAwsSqsClient;
use Bref\Context\Context;
use Bref\Event\Sqs\SqsEvent;
use Bref\Event\Sqs\SqsRecord;
use EonX\EasyServerless\Messenger\SqsHandler\AbstractSqsHandler;
use EonX\EasyServerless\Tests\Unit\AbstractUnitTestCase;

final class SqsHandlerTest extends AbstractUnitTestCase
{
    protected function tearDown(): void
    {
        \putenv('APP_RUNTIME_MODE');
        unset($_ENV['APP_RUNTIME_MODE'], $_SERVER['APP_RUNTIME_MODE']);

        parent::tearDown();
    }

    public function testHandleSqsDoesNotOverrideExplicitRuntimeMode(): void
    {
        \putenv('APP_RUNTIME_MODE=custom=1');
        $_ENV['APP_RUNTIME_MODE'] = $_SERVER['APP_RUNTIME_MODE'] = 'custom=1';

        $sut = $this->createSqsHandler();

        $sut->handleSqs($this->createSqsEvent(), Context::fake());

        self::assertSame('custom=1', \getenv('APP_RUNTIME_MODE'));
        self::assertSame('custom=1', $_ENV['APP_RUNTIME_MODE']);
        self::assertSame('custom=1', $_SERVER['APP_RUNTIME_MODE']);
    }

    public function testHandleSqsSetsWorkerRuntimeModeWhenMissing(): void
    {
        $sut = $this->createSqsHandler();

        $sut->handleSqs($this->createSqsEvent(), Context::fake());

        self::assertSame('worker=1', \getenv('APP_RUNTIME_MODE'));
        self::assertSame('worker=1', $_ENV['APP_RUNTIME_MODE']);
        self::assertSame('worker=1', $_SERVER['APP_RUNTIME_MODE']);
    }

    private function createSqsEvent(): SqsEvent
    {
        return new SqsEvent(['Records' => []]);
    }

    private function createSqsHandler(): AbstractSqsHandler
    {
        $sqsClient = $this->createMock(AsyncAwsSqsClient::class);

        return new class($sqsClient) extends AbstractSqsHandler {
            public function __construct(
                private readonly AsyncAwsSqsClient $sqsClient,
            ) {
                parent::__construct();
            }

            protected function getSqsClient(): AsyncAwsSqsClient
            {
                return $this->sqsClient;
            }

            protected function handleSqsRecords(SqsRecord $sqsRecord, Context $context): void
            {
            }
        };
    }
}
