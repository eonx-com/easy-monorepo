<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Fixture\App\ReturnNotFoundOnReadOperations\Controller;

use stdClass;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class IncomingWebhookController
{
    public function __invoke(string $someExtraVariable): stdClass
    {
        return new stdClass();
    }
}
