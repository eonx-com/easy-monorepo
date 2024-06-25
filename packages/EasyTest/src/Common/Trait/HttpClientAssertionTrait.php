<?php
declare(strict_types=1);

namespace EonX\EasyTest\Common\Trait;

use EonX\EasyTest\HttpClient\HttpClient\HttpClientStub;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;

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
        $httpClient = KernelTestCase::getContainer()->get(
            \sprintf('%s $%s', HttpClientInterface::class, $name)
        );

        return $httpClient;
    }
}
