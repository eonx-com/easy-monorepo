<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Application\Common\ReturnNotFoundOnReadOperation;

use EonX\EasyApiPlatform\Tests\Application\AbstractApplicationTestCase;

final class ReturnNotFoundOnReadOperationTest extends AbstractApplicationTestCase
{
    public function testItSucceeds(): void
    {
        $this->initDatabase();

        $response = self::$client->request(
            'POST',
            '/questions/1/mark-as-answered',
            [
                'headers' => [
                    'content-type' => 'application/json',
                ],
            ]
        );

        self::assertSame(404, $response->getStatusCode());
    }

    public function testItSucceedsWhenOperationWithoutRead(): void
    {
        $this->initDatabase();

        $response = self::$client->request(
            'POST',
            '/incoming-webhooks/some-value',
            [
                'headers' => [
                    'content-type' => 'application/json',
                ],
                'json' => [],
            ]
        );

        self::assertSame(204, $response->getStatusCode());
    }

    public function testItSucceedsWhenReturnNotFoundOnReadOperationsIsDisabled(): void
    {
        self::setUpClient(['environment' => 'disable_return_not_found_on_read_operations']);
        $this->initDatabase();

        $response = self::$client->request(
            'POST',
            '/questions/1/mark-as-answered',
            [
                'headers' => [
                    'content-type' => 'application/json',
                ],
            ]
        );
        self::assertSame(500, $response->getStatusCode());
        $responseData = \json_decode($response->getContent(false), true);
        self::assertSame(403, $responseData['custom_code']);
    }

    public function testItSucceedsWithoutUriVariables(): void
    {
        $this->initDatabase();

        $response = self::$client->request(
            'POST',
            '/questions',
            [
                'headers' => [
                    'content-type' => 'application/json',
                ],
                'json' => [],
            ]
        );

        self::assertSame(201, $response->getStatusCode());
        $responseData = \json_decode($response->getContent(false), true);
        self::assertSame(1, $responseData['id']);
    }
}
