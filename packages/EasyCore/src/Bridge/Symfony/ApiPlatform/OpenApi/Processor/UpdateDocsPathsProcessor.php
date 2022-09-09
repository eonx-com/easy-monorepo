<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\OpenApi\Processor;

use EonX\EasyCore\Bridge\Symfony\ApiPlatform\OpenApi\Provider\OpenApiContextProvider;

final class UpdateDocsPathsProcessor implements DecorationProcessorInterface, DefaultDecorationProcessorInterface
{
    /**
     * @param string[] $endpointsRemoveParams
     * @param string[] $endpointsRemoveBody
     * @param string[] $endpointsRemoveResponse
     * @param string[] $skipMethodNames
     */
    public function __construct(
        private OpenApiContextProvider $openApiContextProvider,
        private array $endpointsRemoveParams,
        private array $endpointsRemoveBody,
        private array $endpointsRemoveResponse,
        private array $skipMethodNames
    ) {
    }

    public function process(array $documentation): array
    {
        foreach ($documentation['paths'] as $path => &$methods) {
            foreach ($methods as $methodName => &$method) {
                if (\in_array($methodName, $this->skipMethodNames, true) === true) {
                    continue;
                }

                $apiResourceName = $method['tags'][0];
                $method['tags'][0] = $this->openApiContextProvider->getTag($apiResourceName);

                $this->updateSummary($method, $apiResourceName);

                $this->removeAttributes($method, (string)$path);

                // Make response descriptions more generic
                $this->updateDescription($method['responses'], $apiResourceName, $method['operationId']);
            }
            unset($method);
        }
        unset($methods);

        return $documentation;
    }

    /**
     * @param mixed[] $method
     */
    private function removeAttributes(array &$method, string $path): void
    {
        // Remove path descriptions - API platform makes them the same as the summary
        unset($method['description']);

        // Remove request body descriptions - Readme.com doesn't display them anyway
        unset($method['requestBody']['description']);

        // Remove unnecessary request bodies for specific POST endpoints
        if (\count($this->endpointsRemoveBody) > 0) {
            $clearRequestsFor = \implode('|', $this->endpointsRemoveBody);
            if (\preg_match('#/(' . $clearRequestsFor . ')$#', (string)$path)) {
                unset($method['requestBody']);
            }
        }

        // API Platform keeps these parameters despite embedded openapi_context setting them to empty
        if (\count($this->endpointsRemoveParams) > 0) {
            $clearParametersFor = \implode('|', $this->endpointsRemoveParams);
            if (\preg_match('/\/(' . $clearParametersFor . ')$/', (string)$path)) {
                unset($method['parameters']);
            }
        }

        // Remove responses for POST methods that return 200 instead of 201
        if (\count($this->endpointsRemoveResponse) > 0) {
            $clearResponsesFor = \implode('|', $this->endpointsRemoveResponse);
            if (
                isset($method['responses']['201'])
                && \preg_match('/\/(' . $clearResponsesFor . ')/', (string)$path)
            ) {
                unset($method['responses']['201']);
            }
        }
    }

    /**
     * @param mixed[] $responses
     * @param non-empty-string $resourceName
     */
    private function updateDescription(array &$responses, string $resourceName, string $operationId): void
    {
        /** @var string[] $operation */
        $operation = \explode($resourceName, $operationId);
        $responsesDescriptions = $this->openApiContextProvider->getResponsesDescriptions(
            resourceName: $resourceName,
            applyTo: \strtolower($operation[1]),
            operationName: $operation[0]
        );

        foreach ($responsesDescriptions as $code => $description) {
            $responses[$code]['description'] = $description;
        }
    }

    /**
     * @param mixed[] $method
     * @param non-empty-string $resourceName
     */
    private function updateSummary(array &$method, string $resourceName): void
    {
        /** @var string[] $operation */
        $operation = \explode($resourceName, (string)$method['operationId']);
        $method['summary'] = $this->openApiContextProvider->getOperationSummary(
            resourceName: $resourceName,
            applyTo: \strtolower($operation[1]),
            operationName: $operation[0],
        );
    }
}
