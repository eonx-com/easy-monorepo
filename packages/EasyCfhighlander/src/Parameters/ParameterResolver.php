<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyCfhighlander\Parameters;

use LoyaltyCorp\EasyCfhighlander\Interfaces\ParameterResolverInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

final class ParameterResolver implements ParameterResolverInterface
{
    /** @var string */
    private $cacheFile;

    /** @var \Symfony\Component\Filesystem\Filesystem */
    private $filesystem;

    /** @var \Symplify\PackageBuilder\Parameter\ParameterProvider */
    private $parameterProvider;

    /** @var callable[] */
    private $resolvers = [];

    /**
     * ParameterResolver constructor.
     *
     * @param \Symplify\PackageBuilder\Parameter\ParameterProvider $parameterProvider
     * @param null|string $cacheFile
     */
    public function __construct(ParameterProvider $parameterProvider, ?string $cacheFile = null)
    {
        $this->filesystem = new Filesystem();
        $this->parameterProvider = $parameterProvider;
        $this->cacheFile = $cacheFile ?? __DIR__ . '/../../var/last_params.yaml';
    }

    /**
     * Add resolver callable.
     *
     * @param callable $resolver
     *
     * @return \LoyaltyCorp\EasyCfhighlander\Interfaces\ParameterResolverInterface
     */
    public function addResolver(callable $resolver): ParameterResolverInterface
    {
        $this->resolvers[] = $resolver;

        return $this;
    }

    /**
     * Resolve parameters.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     *
     * @return mixed[]
     */
    public function resolve(InputInterface $input): array
    {
        $params = $this->resolveDefaultParameters();

        foreach ($this->resolvers as $resolver) {
            $params = \array_merge($params, \call_user_func($resolver, $params));
        }

        $params = \array_merge($params, $input->getArguments(), $input->getOptions());

        $this->filesystem->dumpFile($this->cacheFile, Yaml::dump($params));

        return $params;
    }

    /**
     * Resolve default parameters from cache and config files.
     *
     * @return mixed[]
     */
    private function resolveDefaultParameters(): array
    {
        $params = [];

        if ($this->filesystem->exists($this->cacheFile)) {
            $params = Yaml::parseFile($this->cacheFile);
        }

        return \array_merge($params, $this->parameterProvider->provide());
    }
}
