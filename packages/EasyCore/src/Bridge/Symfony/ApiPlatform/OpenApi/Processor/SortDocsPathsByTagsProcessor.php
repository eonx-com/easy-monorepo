<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\OpenApi\Processor;

final class SortDocsPathsByTagsProcessor implements DecorationProcessorInterface, DefaultDecorationProcessorInterface
{
    public function process(array $documentation): array
    {
        $paths = &$documentation['paths'];

        \uksort(
            $paths,
            static fn (string $key1, string $key2): int => [
                \strtolower((string)\current($paths[$key1])['tags'][0]),
                $key1,
            ] <=> [
                \strtolower((string)\current($paths[$key2])['tags'][0]),
                $key2,
            ]
        );

        return $documentation;
    }
}
