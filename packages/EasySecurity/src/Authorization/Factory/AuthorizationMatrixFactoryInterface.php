<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Authorization\Factory;

use EonX\EasySecurity\Authorization\Provider\AuthorizationMatrixProviderInterface;

interface AuthorizationMatrixFactoryInterface
{
    public function create(): AuthorizationMatrixProviderInterface;
}
