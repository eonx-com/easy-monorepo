<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Migration\Migration;

interface ExpectedConnectionAwareMigrationInterface
{
    public function getExpectedConnectionName(): string;

    public function setExpectedConnection(object $expectedConnection): void;
}
