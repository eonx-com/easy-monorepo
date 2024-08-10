<?php
declare(strict_types=1);

namespace EonX\EasyTest\Common\Trait;

use EonX\EasyTest\HttpClient\HttpClient\HttpClientStub;
use PHPUnit\Framework\Assert;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @deprecated Since 6.0, will be removed in 7.0. Use TestResponseFactory instead.
 *
 * @mixin \Symfony\Bundle\FrameworkBundle\Test\KernelTestCase
 */
trait HttpClientAssertionTrait
{
    public function assertAllHttpRequestsAreMade(string $httpClientName): void
    {
        Assert::assertFalse(
            $this->getHttpClientStub($httpClientName)
                ->hasUnusedResponses(),
            "Not all requests of the [{$httpClientName}] HTTP client were made."
        );
    }

    public function getHttpClientStub(string $name): HttpClientStub
    {
        /** @var \EonX\EasyTest\HttpClient\HttpClient\HttpClientStub $httpClient */
        $httpClient = self::getContainer()->get(
            \sprintf('%s $%s', HttpClientInterface::class, $name)
        );

        return $httpClient;
    }
}
