<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\Factories;

use EonX\EasyApiToken\Interfaces\EasyApiTokenInterface;
use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixInterface;
use EonX\EasySecurity\MainSecurityContextConfigurator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class MainSecurityContextConfiguratorFactory
{
    /**
     * @var null|\EonX\EasyApiToken\Interfaces\EasyApiTokenInterface
     */
    private $apiToken;

    /**
     * @var \EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixInterface
     */
    private $authorizationMatrix;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    public function __construct(
        AuthorizationMatrixInterface $authorizationMatrix,
        RequestStack $requestStack,
        ?EasyApiTokenInterface $apiToken = null
    ) {
        $this->authorizationMatrix = $authorizationMatrix;
        $this->requestStack = $requestStack;
        $this->apiToken = $apiToken;
    }

    public function __invoke(): MainSecurityContextConfigurator
    {
        return new MainSecurityContextConfigurator(
            $this->authorizationMatrix,
            $this->getRequest(),
            $this->apiToken
        );
    }

    private function getFakeRequest(): Request
    {
        return new Request([], [], [], [], [], ['HTTP_HOST' => 'eonx.com']);
    }

    private function getRequest(): Request
    {
        // Fake request when running in console
        if (\getenv('LINES') && \getenv('COLUMNS')) {
            return $this->getFakeRequest();
        }

        return $this->requestStack->getMasterRequest() ?? $this->getFakeRequest();
    }
}
