<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Laravel\Kernels;

use Illuminate\Contracts\Http\Kernel;
use Laravel\Lumen\Application;
use Symfony\Component\HttpFoundation\Response;

final readonly class LumenHttpKernel implements Kernel
{
    public function __construct(
        private Application $app,
    ) {
    }

    public function bootstrap(): void
    {
        throw new \RuntimeException('Method should not be called.');
    }

    public function getApplication()
    {
        throw new \RuntimeException('Method should not be called.');
    }

    public function handle($request): Response
    {
        return $this->app->handle($request);
    }

    public function terminate($request, $response): void
    {
        $this->app->terminate();
    }
}
