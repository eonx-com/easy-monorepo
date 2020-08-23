<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\Factories;

use EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface;
use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixInterface;
use EonX\EasySecurity\MainSecurityContextConfigurator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class MainSecurityContextConfiguratorFactory
{
    /**
     * @var \EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface
     */
    private $apiTokenDecoder;

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
        ApiTokenDecoderInterface $apiTokenDecoder,
        RequestStack $requestStack
    ) {
        $this->authorizationMatrix = $authorizationMatrix;
        $this->apiTokenDecoder = $apiTokenDecoder;
        $this->requestStack = $requestStack;
    }

    public function __invoke(): MainSecurityContextConfigurator
    {
        $request = $this->getRequest();

        return new MainSecurityContextConfigurator(
            $this->authorizationMatrix,
            $request,
            $this->apiTokenDecoder->decode($request)
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
