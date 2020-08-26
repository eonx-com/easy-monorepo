<?php

declare(strict_types=1);

namespace EonX\EasySecurity;

use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixInterface;
use EonX\EasySecurity\Interfaces\SecurityContextFactoryInterface;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;

abstract class AbstractSecurityContextFactory implements SecurityContextFactoryInterface
{
    /**
     * @var \EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixInterface
     */
    private $authorizationMatrix;

    public function __construct(AuthorizationMatrixInterface $authorizationMatrix)
    {
        $this->authorizationMatrix = $authorizationMatrix;
    }

    public function create(): SecurityContextInterface
    {
        $context = $this->doCreate();
        $context->setAuthorizationMatrix($this->authorizationMatrix);

        return $context;
    }

    abstract protected function doCreate(): SecurityContextInterface;
}
