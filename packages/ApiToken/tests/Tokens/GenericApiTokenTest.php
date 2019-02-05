<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Tests\Tokens;

use StepTheFkUp\ApiToken\Tests\AbstractTestCase;
use StepTheFkUp\ApiToken\Tokens\GenericApiToken;

final class GenericApiTokenTest extends AbstractTestCase
{
    /**
     * ApiToken should return exactly input data.
     *
     * @return void
     */
    public function testGetters(): void
    {
        $payload = ['key1' => 'value1', 10 => 'value10'];
        $strategy = 'generic-token-strategy';

        $genericToken = new GenericApiToken($payload, $strategy);

        self::assertEquals($payload, $genericToken->getPayload());
        self::assertEquals($strategy, $genericToken->getStrategy());
    }
}