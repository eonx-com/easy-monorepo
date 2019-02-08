<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\ServerRequestFactory;

/**
 * This class has for objective to provide common features to all tests without having to update
 * the class they all extend.
 */
abstract class AbstractTestCase extends TestCase
{
    /**
     * Create PSR ServerRequest for given server array.
     *
     * @param null|mixed[] $server
     * @param null|mixed[] $query
     *
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    protected function createServerRequest(?array $server = null, ?array $query = null): ServerRequestInterface
    {
        return ServerRequestFactory::fromGlobals($server ?? [], $query ?? []);
    }
}