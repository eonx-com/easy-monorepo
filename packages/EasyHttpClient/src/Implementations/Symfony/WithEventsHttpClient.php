<?php

declare(strict_types=1);

namespace EonX\EasyHttpClient\Implementations\Symfony;

use Carbon\Carbon;
use Closure;
use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;
use EonX\EasyHttpClient\Data\Config;
use EonX\EasyHttpClient\Data\RequestData;
use EonX\EasyHttpClient\Data\ResponseData;
use EonX\EasyHttpClient\Events\HttpRequestSentEvent;
use EonX\EasyHttpClient\Interfaces\HttpOptionsInterface;
use EonX\EasyHttpClient\Interfaces\RequestDataInterface;
use EonX\EasyHttpClient\Interfaces\RequestDataModifierInterface;
use EonX\EasyHttpClient\Interfaces\ResponseDataInterface;
use EonX\EasyUtils\Helpers\CollectorHelper;
use Symfony\Component\HttpClient\AsyncDecoratorTrait;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Response\AsyncContext;
use Symfony\Component\HttpClient\Response\AsyncResponse;
use Symfony\Contracts\HttpClient\ChunkInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;

final class WithEventsHttpClient implements HttpClientInterface
{
    use AsyncDecoratorTrait;

    /**
     * @param null|iterable<\EonX\EasyHttpClient\Interfaces\RequestDataModifierInterface> $modifiers
     * @param null|string[] $modifiersWhitelist
     */
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        ?HttpClientInterface $decorated = null,
        private readonly ?iterable $modifiers = [],
        private readonly ?bool $modifiersEnabled = true,
        private readonly ?array $modifiersWhitelist = [],
    ) {
        $this->client = $decorated ?? HttpClient::create();
    }

    /**
     * @param null|mixed[] $options
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws \Throwable
     */
    public function request(string $method, string $url, ?array $options = null): ResponseInterface
    {
        $config = $this->resolveConfigFromHttpOptions($options ?? []);
        $requestData = new RequestData($method, $config->getHttpClientOptions(), Carbon::now('UTC'), $url);

        if ($config->isRequestDataModifiersEnabled()) {
            $requestData = $this->modifyRequestData(
                $requestData,
                $config->getRequestDataModifiersWhitelist(),
                $config->getRequestDataModifiers()
            );
        }

        try {
            return new AsyncResponse(
                $this->client,
                $requestData->getMethod(),
                $requestData->getUrl(),
                $requestData->getOptions(),
                $this->getPassThruClosure($requestData, $config)
            );
        } catch (Throwable $throwable) {
            $this->dispatchEvent($config, $requestData, throwable: $throwable);

            throw $throwable;
        }
    }

    private function dispatchEvent(
        Config $config,
        RequestDataInterface $requestData,
        ?ResponseDataInterface $responseData = null,
        ?Throwable $throwable = null,
    ): void {
        if ($config->isEventsEnabled()) {
            $this->eventDispatcher->dispatch(new HttpRequestSentEvent(
                $requestData,
                $responseData,
                $throwable,
                $throwable !== null ? Carbon::now('UTC') : null,
                $config->getRequestDataExtra()
            ));
        }
    }

    private function getPassThruClosure(RequestDataInterface $requestData, Config $config): Closure
    {
        return function (ChunkInterface $chunk, AsyncContext $asyncContext) use ($requestData, $config): iterable {
            // Get chunk content here, so we can handle transport/timeout exceptions
            try {
                $chunkContent = $chunk->getContent();
            } catch (Throwable $throwable) {
                $this->dispatchEvent($config, $requestData, throwable: $throwable);

                throw $throwable;
            }

            if ($chunkContent !== '') {
                $asyncContext->setInfo(
                    'temp_content',
                    ($asyncContext->getInfo('temp_content') ?? '') . $chunk->getContent()
                );
            }

            if ($chunk->isLast()) {
                $this->dispatchEvent($config, $requestData, new ResponseData(
                    (string)($asyncContext->getInfo('temp_content') ?? ''),
                    $asyncContext->getHeaders(),
                    Carbon::now('UTC'),
                    $asyncContext->getStatusCode()
                ));
            }

            yield $chunk;
        };
    }

    /**
     * @param string[] $modifiersWhitelist
     * @param \EonX\EasyHttpClient\Interfaces\RequestDataModifierInterface[] $modifiers
     */
    private function modifyRequestData(
        RequestDataInterface $data,
        array $modifiersWhitelist,
        array $modifiers,
    ): RequestDataInterface {
        // No explicitly allowed modifiers, execute all of them
        if (\count($modifiersWhitelist) < 1) {
            foreach ($modifiers as $modifier) {
                $data = $modifier->modifyRequestData($data);
            }

            return $data;
        }

        // Execute only allowed modifiers
        foreach ($modifiers as $modifier) {
            foreach ($modifiersWhitelist as $allowedModifier) {
                if (\is_a($modifier, $allowedModifier, true)) {
                    $data = $modifier->modifyRequestData($data);
                }
            }
        }

        return $data;
    }

    /**
     * @param mixed[] $options
     */
    private function resolveConfigFromHttpOptions(array $options): Config
    {
        $extra = $options[HttpOptionsInterface::REQUEST_DATA_EXTRA] ?? null;

        $modifiers = $this->modifiers ?? [];
        $modifiers = \is_array($modifiers) ? $modifiers : \iterator_to_array($modifiers);
        $modifiers = \array_merge($modifiers, $options[HttpOptionsInterface::REQUEST_DATA_MODIFIERS] ?? []);

        $modifiersEnabled = $this->modifiersEnabled ??
            $options[HttpOptionsInterface::REQUEST_DATA_MODIFIERS_ENABLED] ?? true;

        $modifiersWhitelist = \array_merge(
            $this->modifiersWhitelist ?? [],
            $options[HttpOptionsInterface::REQUEST_DATA_MODIFIERS_WHITELIST] ?? []
        );

        $eventsEnabled = $options[HttpOptionsInterface::EVENTS_ENABLED] ?? true;

        unset(
            $options[HttpOptionsInterface::EVENTS_ENABLED],
            $options[HttpOptionsInterface::REQUEST_DATA_EXTRA],
            $options[HttpOptionsInterface::REQUEST_DATA_MODIFIERS],
            $options[HttpOptionsInterface::REQUEST_DATA_MODIFIERS_ENABLED],
            $options[HttpOptionsInterface::REQUEST_DATA_MODIFIERS_WHITELIST]
        );

        return new Config(
            $options,
            $extra,
            CollectorHelper::filterByClassAsArray($modifiers, RequestDataModifierInterface::class),
            $modifiersWhitelist,
            (bool)$modifiersEnabled,
            (bool)$eventsEnabled
        );
    }
}
