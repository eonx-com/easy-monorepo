<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Tests\Stub\Service;

use Symfony\Contracts\Service\ResetInterface;

final class ServicesResetterStub implements ResetInterface
{
    public function reset(): void
    {
    }
}
