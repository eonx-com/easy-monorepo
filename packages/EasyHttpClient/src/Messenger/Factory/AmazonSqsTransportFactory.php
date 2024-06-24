<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Messenger\Factory;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Bridge\AmazonSqs\Transport\AmazonSqsTransport;
use Symfony\Component\Messenger\Bridge\AmazonSqs\Transport\AmazonSqsTransportFactory as BaseAmazonSqsTransportFactory;
use Symfony\Component\Messenger\Bridge\AmazonSqs\Transport\Connection;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class AmazonSqsTransportFactory extends BaseAmazonSqsTransportFactory
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly ?LoggerInterface $logger = null,
    ) {
        parent::__construct();
    }

    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        unset($options['transport_name']);

        return new AmazonSqsTransport(
            Connection::fromDsn($dsn, $options, $this->httpClient, $this->logger),
            $serializer
        );
    }
}
