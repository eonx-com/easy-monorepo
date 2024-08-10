<?php
declare(strict_types=1);

namespace EonX\EasyTest\HttpClient\Trait;

use EonX\EasyTest\HttpClient\Factory\TestResponseFactory;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;

/**
 * @mixin \PHPUnit\Framework\TestCase
 */
trait HttpClientCommonTestTrait
{
    #[Before]
    public function setUpHttpClient(): void
    {
        TestResponseFactory::reset();
    }

    #[After]
    public function tearDownHttpClient(): void
    {
        self::assertTrue(TestResponseFactory::areAllResponsesUsed(), 'Not all responses were used.');
    }
}
