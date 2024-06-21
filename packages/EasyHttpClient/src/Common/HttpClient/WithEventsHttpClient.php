<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Common\HttpClient;

use Carbon\Carbon;
use Closure;
use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;
use EonX\EasyHttpClient\Common\Enum\HttpOption;
use EonX\EasyHttpClient\Common\Event\HttpRequestSentEvent;
use EonX\EasyHttpClient\Common\Modifier\RequestDataModifierInterface;
use EonX\EasyHttpClient\Common\ValueObject\Config;
use EonX\EasyHttpClient\Common\ValueObject\RequestData;
use EonX\EasyHttpClient\Common\ValueObject\RequestDataInterface;
use EonX\EasyHttpClient\Common\ValueObject\ResponseData;
use EonX\EasyHttpClient\Common\ValueObject\ResponseDataInterface;
use EonX\EasyUtils\Helpers\CollectorHelper;
use Symfony\Component\HttpClient\AsyncDecoratorTrait;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Response\AsyncContext;
use Symfony\Component\HttpClient\Response\AsyncResponse;
use Symfony\Contracts\HttpClient\ChunkInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\Service\ResetInterface;
use Throwable;

final class WithEventsHttpClient implements HttpClientInterface, ResetInterface
{
    use AsyncDecoratorTrait;

    /**
     * @param iterable<\EonX\EasyHttpClient\Common\Modifier\RequestDataModifierInterface>|null $modifiers
     * @param string[]|null $modifiersWhitelist
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
     * @param \EonX\EasyHttpClient\Common\Modifier\RequestDataModifierInterface[] $modifiers
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

    private function resolveConfigFromHttpOptions(array $options): Config
    {
        $extra = $options[HttpOption::RequestDataExtra->value] ?? null;

        $modifiers = $this->modifiers ?? [];
        $modifiers = \is_array($modifiers) ? $modifiers : \iterator_to_array($modifiers);
        $modifiers = \array_merge($modifiers, $options[HttpOption::RequestDataModifiers->value] ?? []);

        $modifiersEnabled = $this->modifiersEnabled ??
            $options[HttpOption::RequestDataModifiersEnabled->value] ?? true;

        $modifiersWhitelist = \array_merge(
            $this->modifiersWhitelist ?? [],
            $options[HttpOption::RequestDataModifiersWhitelist->value] ?? []
        );

        $eventsEnabled = $options[HttpOption::EventsEnabled->value] ?? true;

        unset(
            $options[HttpOption::EventsEnabled->value],
            $options[HttpOption::RequestDataExtra->value],
            $options[HttpOption::RequestDataModifiers->value],
            $options[HttpOption::RequestDataModifiersEnabled->value],
            $options[HttpOption::RequestDataModifiersWhitelist->value]
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
