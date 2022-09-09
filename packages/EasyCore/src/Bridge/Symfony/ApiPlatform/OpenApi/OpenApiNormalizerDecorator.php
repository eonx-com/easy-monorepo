<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\OpenApi;

use EonX\EasyCore\Bridge\Symfony\ApiPlatform\OpenApi\Provider\OpenApiProcessorsProvider;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class OpenApiNormalizerDecorator implements NormalizerInterface
{
    public function __construct(
        private OpenApiProcessorsProvider $processorsProvider,
        private NormalizerInterface $decorated,
        private string $baseApiUri,
        private string $environment
    ) {
    }

    /**
     * @param mixed $object
     * @param mixed[]|null $context
     *
     * @return mixed[]
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function normalize($object, ?string $format = null, ?array $context = null): array
    {
        /** @var mixed[] $docs */
        $docs = $this->decorated->normalize($object, $format, $context ?? []);

        // Add a servers tag to allow 'Try it' functionality in Readme.com
        $docs['servers'][0]['url'] = $this->baseApiUri;
        $docs['servers'][0]['description'] = \mb_strtoupper($this->environment);

        $processors = $this->processorsProvider->provide();
        foreach ($processors as $processor) {
            $docs = $processor->process($docs);
        }

        return $docs;
    }

    /**
     * @param mixed $data
     */
    public function supportsNormalization($data, ?string $format = null): bool
    {
        return $this->decorated->supportsNormalization($data, $format);
    }
}
