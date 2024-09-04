<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Migration\Migration;

trait ExpectedConnectionAwareMigrationTrait
{
    private object $expectedConnection;

    public function setExpectedConnection(object $expectedConnection): void
    {
        $this->expectedConnection = $expectedConnection;
    }
}
