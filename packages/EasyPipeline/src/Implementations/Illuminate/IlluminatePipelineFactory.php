<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyPipeline\Implementations\Illuminate;

use Illuminate\Pipeline\Pipeline;
use Illuminate\Contracts\Container\Container as ContainerInterface;
use StepTheFkUp\EasyPipeline\Exceptions\InvalidMiddlewareProviderException;
use StepTheFkUp\EasyPipeline\Exceptions\PipelineNotFoundException;
use StepTheFkUp\EasyPipeline\Interfaces\MiddlewareProviderInterface;
use StepTheFkUp\EasyPipeline\Interfaces\PipelineFactoryInterface;
use StepTheFkUp\EasyPipeline\Interfaces\PipelineInterface;

final class IlluminatePipelineFactory implements PipelineFactoryInterface
{
    /**
     * @var \Illuminate\Contracts\Container\Container
     */
    private $container;

    /**
     * @var string[]
     */
    private $mapping;

    /**
     * @var null|string
     */
    private $prefix;

    /**
     * @var \StepTheFkUp\EasyPipeline\Interfaces\PipelineInterface[]
     */
    private $resolved = [];

    /**
     * IlluminatePipelineFactory constructor.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     * @param array $mapping
     * @param null|string $prefix
     */
    public function __construct(ContainerInterface $container, array $mapping, ?string $prefix = null)
    {
        $this->container = $container;
        $this->mapping = $mapping;
        $this->prefix = $prefix;
    }

    /**
     * Create pipeline for given name and input.
     *
     * @param string $pipeline The pipeline name
     * @param mixed $input The input to process
     *
     * @return \StepTheFkUp\EasyPipeline\Interfaces\PipelineInterface
     *
     * @throws \StepTheFkUp\EasyPipeline\Exceptions\PipelineNotFoundException If given pipeline not found
     */
    public function create(string $pipeline, $input): PipelineInterface
    {
        if (isset($this->resolved[$pipeline])) {
            $pipeline = $this->resolved[$pipeline];

            return $pipeline->setInput($input);
        }

        return $this->resolved[$pipeline] = (new IlluminatePipeline(new Pipeline($this->container)))
            ->setInput($input)
            ->setMiddlewareList($this->createMiddlewareProvider($pipeline)->getMiddlewareList());
    }

    /**
     * Create middleware provider for given pipeline.
     *
     * @param string $pipeline
     *
     * @return \StepTheFkUp\EasyPipeline\Interfaces\MiddlewareProviderInterface
     */
    private function createMiddlewareProvider(string $pipeline): MiddlewareProviderInterface
    {
        $pipelineName = $this->getPipelineName($pipeline);

        if (isset($this->mapping[$pipeline]) === false) {
            throw new PipelineNotFoundException(\sprintf(
                'In %s, no middleware provider configured for pipeline "%s"',
                \get_class($this),
                $pipeline
            ));
        }

        $provider = $this->container->get($this->mapping[$pipelineName]);

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
