<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\OpenApi\Processor;

final class SortDocsQueryParametersProcessor implements
    DecorationProcessorInterface,
    DefaultDecorationProcessorInterface
{
    private const ORDER_PATTERN = '/^order\[.+?\]$/';

    private const PAGE_PARAMETERS = ['page', 'perPage'];

    public function process(array $documentation): array
    {
        foreach ($documentation['paths'] as &$methods) {
            foreach ($methods as $methodName => $method) {
                if (isset($method['parameters']) === false || \is_array($method['parameters']) === false) {
                    continue;
                }

                $pageParameters = [];
                $orderParameters = [];

                foreach ($method['parameters'] as $parameterIndex => $parameter) {
                    if (\in_array($parameter['name'], self::PAGE_PARAMETERS, true)) {
                        $pageParameters[] = $parameter;

                        unset($method['parameters'][$parameterIndex]);
                    }

                    if (\preg_match(self::ORDER_PATTERN, (string)$parameter['name'])) {
                        $orderParameters[] = $parameter;

                        unset($method['parameters'][$parameterIndex]);
                    }
                }

                \usort($pageParameters, static fn (
                    array $parameter1,
                    array $parameter2
                ) => $parameter1['name'] <=> $parameter2['name']);
                \usort($orderParameters, static fn (
                    array $parameter1,
                    array $parameter2
                ) => $parameter1['name'] <=> $parameter2['name']);
                \usort($method['parameters'], static function (
                    array $parameter1,
                    array $parameter2
                ) {
                    $parameter1['name'] = \str_replace(['[', ']'], '!', (string)$parameter1['name']);
                    $parameter2['name'] = \str_replace(['[', ']'], '!', (string)$parameter2['name']);

                    return $parameter1['name'] <=> $parameter2['name'];
                });

                $methods[$methodName]['parameters'] = \array_merge(
                    $pageParameters,
                    $orderParameters,
                    $method['parameters']
                );
            }
        }

        return $documentation;
    }
}
