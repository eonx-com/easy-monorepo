<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Laravel\Helpers;

use EonX\EasySecurity\Bridge\Laravel\Interfaces\DeferredContextResolverInterface;
use EonX\EasySecurity\Interfaces\ContextInterface;
use Illuminate\Contracts\Foundation\Application;

final class DeferredContextResolver implements DeferredContextResolverInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $app;

    /**
     * @var string
     */
    private $contextServiceId;

    /**
     * DeferredContextResolver constructor.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @param string $contextServiceId
     */
    public function __construct(Application $app, string $contextServiceId)
    {
        $this->app = $app;
        $this->contextServiceId = $contextServiceId;
    }

    /**
     * Resolve context.
     *
     * @return \EonX\EasySecurity\Interfaces\ContextInterface
     */
    public function resolve(): ContextInterface
    {
        /** @var \EonX\EasySecurity\Interfaces\ContextInterface $context */
        $context = $this->app->get($this->contextServiceId);

        return $context;
    }
}
