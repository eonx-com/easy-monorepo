<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Application\Common\Return404OnPost;

use EonX\EasyApiPlatform\Tests\Application\AbstractApplicationTestCase;

final class Return404OnPostTest extends AbstractApplicationTestCase
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

    public function testItSucceedsWhenReturn404OnPostDisabled(): void
    {
        self::setUpClient(['environment' => 'return_404_on_post', 'debug' => true]);
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
        dump($responseData);
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
