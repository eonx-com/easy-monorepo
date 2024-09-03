<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Migration\Migration;

interface EnvironmentAwareMigrationInterface
{
    public function setEnvironment(string $environment): void;
}
