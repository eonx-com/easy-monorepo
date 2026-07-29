<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Stub\Kernel;

use EonX\EasyServerless\Aws\Kernel\AbstractServerlessHttpKernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpFoundation\Request;

final class ServerlessHttpKernelStub extends AbstractServerlessHttpKernel
{
    public function registerBundles(): iterable
    {
        return [];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        // No container needed for these tests
    }

    public function trustApiGatewayProxyForTest(Request $request): void
    {
        $this->trustApiGatewayProxy($request);
    }
}
