<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces\Authorization;

interface AuthorizationMatrixFactoryInterface
{
    public function create(): AuthorizationMatrixInterface;
}
