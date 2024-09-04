<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Migration\Migration;

trait EnvironmentAwareMigrationTrait
{
    private string $environment;

    public function setEnvironment(string $environment): void
    {
        $this->environment = $environment;
    }
}
