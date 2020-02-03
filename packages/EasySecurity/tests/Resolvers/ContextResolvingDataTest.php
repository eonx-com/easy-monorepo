<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Resolvers;

use EonX\EasySecurity\Resolvers\ContextResolvingData;
use EonX\EasySecurity\Tests\AbstractTestCase;
use Symfony\Component\HttpFoundation\Request;

final class ContextResolvingDataTest extends AbstractTestCase
{
    /**
     * Cover context resolving data.
     *
     * @return void
     */
    public function testContextResolvingData(): void
    {
        $request = new Request();
        $data = new ContextResolvingData($request);

        self::assertSame($request, $data->getRequest());
    }
}
