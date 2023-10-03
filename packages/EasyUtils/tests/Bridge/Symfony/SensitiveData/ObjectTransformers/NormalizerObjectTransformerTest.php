<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Bridge\Symfony\SensitiveData\ObjectTransformers;


use DateTime;
use EonX\EasyUtils\Bridge\Symfony\SensitiveData\ObjectTransformers\NormalizerObjectTransformer;
use EonX\EasyUtils\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use stdClass;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeZoneNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\UidNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Uid\Uuid;

final class NormalizerObjectTransformerTest extends AbstractTestCase
{
    /**
     * @see testItSucceeds
     */
    public static function provideObjectsToTransform(): iterable
    {
        $dateTime = new DateTime();
        $stdClass = new stdClass();
        $stdClass->foo = 'foo';
        $stdClass->bar = 'bar';
        $stdClass->dateTime = $dateTime;
        yield 'stdClass' => [
            'expected' => ['foo' => 'foo', 'bar' => 'bar', 'dateTime' => $dateTime->format(\DATE_ATOM)],
            'object' => $stdClass,
        ];

        $object = new class($dateTime) {
            public function __construct(
                public DateTime $dateTime,
                private string $foo = 'foo',
                protected string $bar = 'bar',
            ) {
            }

            public function getDateTime(): DateTime
            {
                return $this->dateTime;
            }

            public function getFoo(): string
            {
                return $this->foo;
            }

            public function getBar(): string
            {
                return $this->bar;
            }
        };

        yield 'object' => [
            'expected' => ['bar' => 'bar', 'dateTime' => $dateTime->format(\DATE_ATOM), 'foo' => 'foo'],
            'object' => $object,
        ];

        yield 'datetime' => [
            'expected' => ['datetime' => $dateTime->format(\DATE_ATOM)],
            'object' => $dateTime,
        ];

        $uuid = Uuid::v6();
        yield 'uuid' => [
            'expected' => ['uuid' => $uuid->toRfc4122()],
            'object' => $uuid,
        ];
    }

    #[DataProvider('provideObjectsToTransform')]
    public function testItSucceeds(array $expected, object $object): void
    {
        $container = new Container();
        $container->set(NormalizerInterface::class, $this->arrangeNormalizer());
        $sut = new NormalizerObjectTransformer();
        $sut->setContainer($container);

        $actual = $sut->transform($object);

        self::assertEquals($expected, $actual);
    }

    public function testItSucceedsWhenNormalizerThrowsException(): void
    {
        $object = new MockResponse('invalid', ['http_code' => 500]);
        $container = new Container();
        $container->set(NormalizerInterface::class, $this->arrangeNormalizer());
        $sut = new NormalizerObjectTransformer();
        $sut->setContainer($container);

        $actual = $sut->transform($object);

        self::assertSame([], $actual);
    }

    public function testItSucceedsWithException(): void
    {
        $exception = new RuntimeException('foo');
        $container = new Container();
        $container->set(NormalizerInterface::class, $this->arrangeNormalizer());
        $sut = new NormalizerObjectTransformer();
        $sut->setContainer($container);

        $actual = $sut->transform($exception);

        self::assertSame('foo', $actual['message']);
        self::assertSame(0, $actual['code']);
    }

    private function arrangeNormalizer(): NormalizerInterface
    {
        $objectNormalizer = new ObjectNormalizer(null, null, new PropertyAccessor());
        $serializer = new Serializer([
            new DateTimeNormalizer(),
            new DateTimeZoneNormalizer(),
            new UidNormalizer(),
            $objectNormalizer,
        ]);
        $objectNormalizer->setSerializer($serializer);

        return $serializer;
    }
}
