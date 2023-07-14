<?php

declare(strict_types=1);

namespace EonX\EasyPipeline\Implementations\Illuminate;

use EonX\EasyPipeline\Exceptions\InvalidMiddlewareProviderException;
use EonX\EasyPipeline\Exceptions\PipelineNotFoundException;
use EonX\EasyPipeline\Interfaces\MiddlewareProviderInterface;
use EonX\EasyPipeline\Interfaces\PipelineFactoryInterface;
use EonX\EasyPipeline\Interfaces\PipelineInterface;
use EonX\EasyPipeline\Interfaces\PipelineNameAwareInterface;
use Illuminate\Contracts\Container\Container as ContainerInterface;
use Illuminate\Pipeline\Pipeline;

final class IlluminatePipelineFactory implements PipelineFactoryInterface
{
    /**
     * @var \EonX\EasyPipeline\Interfaces\PipelineInterface[]
     */
    private array $resolved = [];

    /**
     * @param string[] $pipelines
     */
    public function __construct(
        private ContainerInterface $container,
        private array $pipelines,
        private ?string $prefix = null,
    ) {
    }

    public function create(string $pipeline): PipelineInterface
    {
        if (isset($this->resolved[$pipeline])) {
            return $this->resolved[$pipeline];
        }

        $provider = $this->createMiddlewareProvider($pipeline);

        if ($provider instanceof PipelineNameAwareInterface) {
            $provider->setPipelineName($pipeline);
        }

        return $this->resolved[$pipeline] = new IlluminatePipeline(
            new Pipeline($this->container),
            $provider->getMiddlewareList()
        );
    }

    private function createMiddlewareProvider(string $pipeline): MiddlewareProviderInterface
    {
        if (\in_array($pipeline, $this->pipelines, true) === false) {
            throw new PipelineNotFoundException(\sprintf(
                'In %s, no middleware provider configured for pipeline "%s"',
                static::class,
                $pipeline
            ));
        }

        $provider = $this->container->get($this->getPipelineName($pipeline));

        if ($provider instanceof MiddlewareProviderInterface) {
            return $provider;
        }

        throw new InvalidMiddlewareProviderException(\sprintf(
            'In %s, middleware provider "%s" does not implement %s',
            static::class,
            \get_class($provider),
            MiddlewareProviderInterface::class
        ));
    }

    private function getPipelineName(string $pipeline): string
    {
        if ($this->prefix === null) {
            return $pipeline;
        }

        return $this->prefix . $pipeline;
    }
}
