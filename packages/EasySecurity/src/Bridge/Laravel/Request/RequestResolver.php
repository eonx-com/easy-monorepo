<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Laravel\Request;

use EonX\EasySecurity\Interfaces\RequestResolverInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request as IlluminateRequest;
use Symfony\Component\HttpFoundation\Request;

final class RequestResolver implements RequestResolverInterface
{
    /**
     * @var \Illuminate\Contracts\Container\Container
     */
    private $app;

    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    public function getRequest(): Request
    {
        return $this->app->make(IlluminateRequest::class);
    }
}
