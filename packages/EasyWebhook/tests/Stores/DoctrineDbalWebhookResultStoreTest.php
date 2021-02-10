<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Stores;

use Carbon\Carbon;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use EonX\EasyPagination\Data\StartSizeData;
use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyRandom\RandomGenerator;
use EonX\EasyRandom\UuidV4\RamseyUuidV4Generator;
use EonX\EasyWebhook\Bridge\Doctrine\StatementProviders\SqliteStatementProvider;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use EonX\EasyWebhook\Stores\DoctrineDbalWebhookResultStore;
use EonX\EasyWebhook\Tests\AbstractTestCase;
use EonX\EasyWebhook\Webhook;
use EonX\EasyWebhook\WebhookResult;
use Symfony\Component\HttpClient\Response\MockResponse;

final class DoctrineDbalWebhookResultStoreTest extends AbstractTestCase
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $conn;

    /**
     * @return iterable<mixed>
     */
    public function providerTestFindDueWebhooks(): iterable
    {
        yield '0 webhook in store' => [[], 0];

        yield '1 webhook in store but not sendAfter' => [[$this->createWebhookForSendAfter()], 0];

        yield '1 webhook in store and is sendAfter' => [
            [$this->createWebhookForSendAfter(Carbon::now('UTC')->subDay())],
            1,
        ];

        yield 'webhooks in store but only 1 is sendAfter' => [
            [
                $this->createWebhookForSendAfter(Carbon::now('UTC')->subDay()),
                $this->createWebhookForSendAfter(Carbon::now('UTC')->subDay(), WebhookInterface::STATUS_SUCCESS),
                $this->createWebhookForSendAfter(Carbon::now('UTC')->addDay()),
            ],
            1,
        ];
    }

    /**
     * @param \EonX\EasyWebhook\Interfaces\WebhookInterface[] $webhooks
     * @param int $expectedDue
     *
     * @throws \Doctrine\DBAL\Exception
     *
     * @dataProvider providerTestFindDueWebhooks
     */
    public function testFindDueWebhooks(array $webhooks, int $expectedDue): void
    {
        $store = $this->getStore();

        foreach ($webhooks as $webhook) {
            $store->store(new WebhookResult($webhook));
        }

        $dueWebhooks = $store->findDueWebhooks(new StartSizeData(1, 15));

        self::assertCount($expectedDue, $dueWebhooks->getItems());
    }

    public function testFindWebhookResult(): void
    {
        $store = $this->getStore();
        $result = new WebhookResult(Webhook::fromArray([
            WebhookInterface::OPTION_METHOD => WebhookInterface::DEFAULT_METHOD,
            WebhookInterface::OPTION_URL => 'https://eonx.com',
            WebhookInterface::OPTION_HTTP_OPTIONS => [
                'headers' => [
                    'X-My-Header' => 'my-header',
                ],
            ],
        ]), MockResponse::fromRequest('POST', 'http://eonx.com', [], new MockResponse()), new \Exception());

        $store->store($result);

        self::assertInstanceOf(WebhookResultInterface::class, $store->find((string)$result->getWebhook()->getId()));
        self::assertNull($store->find('invalid'));
    }

    public function testStore(): void
    {
        $id = 'my-id';
        $store = $this->getStore();
        $result = new WebhookResult(
            Webhook::create('https://eonx.com', null, WebhookInterface::DEFAULT_METHOD)->id($id)
        );

        // Save new result with set id
        $store->store($result);
        // Update result
        $store->store($result);

        self::assertInstanceOf(WebhookResultInterface::class, $store->find($id));
    }

    protected function setUp(): void
    {
        $conn = $this->getConnection();
        $conn->connect();

        foreach (SqliteStatementProvider::migrateStatements() as $statement) {
            $conn->executeStatement($statement);
        }

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $conn = $this->getConnection();

        foreach (SqliteStatementProvider::rollbackStatements() as $statement) {
            $conn->executeStatement($statement);
        }

        $conn->close();

        parent::tearDown();
    }

    private function createWebhookForSendAfter(
        ?\DateTimeInterface $sendAfter = null,
        ?string $status = null
    ): WebhookInterface {
        $webhook = Webhook::create('https://eonx.com', null, WebhookInterface::DEFAULT_METHOD);

        if ($sendAfter !== null) {
            $webhook->sendAfter($sendAfter);
        }

        if ($status !== null) {
            $webhook->status($status);
        }

        return $webhook;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function getConnection(): Connection
    {
        if ($this->conn !== null) {
            return $this->conn;
        }

        return $this->conn = DriverManager::getConnection([
            'url' => 'sqlite:///:memory:',
        ]);
    }

    private function getRandomGenerator(): RandomGeneratorInterface
    {
        return (new RandomGenerator())->setUuidV4Generator(new RamseyUuidV4Generator());
    }

    private function getStore(): DoctrineDbalWebhookResultStore
    {
        return new DoctrineDbalWebhookResultStore($this->getConnection(), $this->getRandomGenerator());
    }
}
