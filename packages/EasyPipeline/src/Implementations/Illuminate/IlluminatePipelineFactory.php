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
     * @var \Illuminate\Contracts\Container\Container
     */
    private $container;

    /**
     * @var string[]
     */
    private $pipelines;

    /**
     * @var null|string
     */
    private $prefix;

    /**
     * @var \EonX\EasyPipeline\Interfaces\PipelineInterface[]
     */
    private $resolved = [];

    /**
     * IlluminatePipelineFactory constructor.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     * @param string[] $pipelines
     * @param null|string $prefix
     */
    public function __construct(ContainerInterface $container, array $pipelines, ?string $prefix = null)
    {
        $this->container = $container;
        $this->pipelines = $pipelines;
        $this->prefix = $prefix;
    }

    /**
     * Create pipeline for given name and input.
     *
     * @param string $pipeline The pipeline name
     *
     * @return \EonX\EasyPipeline\Interfaces\PipelineInterface
     *
     * @throws \EonX\EasyPipeline\Exceptions\PipelineNotFoundException If given pipeline not found
     */
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

    /**
     * Create middleware provider for given pipeline.
     *
     * @param string $pipeline
     *
     * @return \EonX\EasyPipeline\Interfaces\MiddlewareProviderInterface
     */
    private function createMiddlewareProvider(string $pipeline): MiddlewareProviderInterface
    {
        if (\in_array($pipeline, $this->pipelines, true) === false) {
            throw new PipelineNotFoundException(\sprintf(
                'In %s, no middleware provider configured for pipeline "%s"',
                \get_class($this),
                $pipeline
            ));
        }

        $provider = $this->container->get($this->getPipelineName($pipeline));

        if ($provider instanceof MiddlewareProviderInterface) {
            return $provider;
        }

        throw new InvalidMiddlewareProviderException(\sprintf(
            'In %s, middleware provider "%s" does not implement %s',
            \get_class($this),
            \get_class($provider),
            MiddlewareProviderInterface::class
        ));
    }

    /**
     * Get pipeline name and take care of prefix if set.
     *
     * @param string $pipeline
     *
     * @return string
     */
    private function getPipelineName(string $pipeline): string
    {
        if ($this->prefix === null) {
            return $pipeline;
        }

        return $this->prefix . $pipeline;
    }
}
