<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\Interfaces;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

interface AuthenticationFailureResponseFactoryInterface
{
    public function create(Request $request, ?AuthenticationException $exception = null): Response;
}
