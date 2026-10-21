<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\Laravel\Jobs;

use Aws\MockHandler;
use Aws\Sqs\SqsClient;
use EonX\EasyServerless\Laravel\Jobs\SqsQueueJob;
use EonX\EasyServerless\Tests\Unit\AbstractUnitTestCase;
use Illuminate\Container\Container;
use RuntimeException;

final class SqsQueueJobTest extends AbstractUnitTestCase
{
    public function testAttemptsDefaultsToOneWithoutReceiveCount(): void
    {
        $job = $this->createJob([]);

        self::assertSame(1, $job->attempts());
    }

    public function testAttemptsUsesSqsReceiveCount(): void
    {
        $job = $this->createJob(['ApproximateReceiveCount' => '4']);

        self::assertSame(4, $job->attempts());
    }

    public function testDeleteAndReleaseOnlyFlagTheJob(): void
    {
        $job = $this->createJob(['ApproximateReceiveCount' => '1']);

        $job->release(10);
        $job->delete();

        // The mock handler has no queued result so any SQS call would throw
        self::assertTrue($job->isReleased());
        self::assertTrue($job->isDeleted());
    }

    public function testSetThrowableIsExposed(): void
    {
        $job = $this->createJob(['ApproximateReceiveCount' => '1']);
        $throwable = new RuntimeException('boom');

        $job->setThrowable($throwable);

        self::assertSame($throwable, $job->getThrowable());
    }

    private function createJob(array $attributes): SqsQueueJob
    {
        $sqsClient = new SqsClient([
            'credentials' => false,
            'handler' => new MockHandler(),
            'region' => 'ap-southeast-2',
            'version' => 'latest',
        ]);

        return new SqsQueueJob(
            container: new Container(),
            sqs: $sqsClient,
            job: [
                'Attributes' => $attributes,
                'Body' => '{}',
                'MessageAttributes' => [],
                'MessageId' => 'message-id',
                'ReceiptHandle' => 'receipt-handle',
            ],
            connectionName: 'sqs',
            queue: 'https://sqs.ap-southeast-2.amazonaws.com/123456789012/test',
        );
    }
}
