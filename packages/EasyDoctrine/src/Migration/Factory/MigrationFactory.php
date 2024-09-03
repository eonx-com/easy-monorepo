<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Migration\Factory;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Version\MigrationFactory as DoctrineMigrationFactory;
use Doctrine\Persistence\ManagerRegistry;
use EonX\EasyDoctrine\Migration\Migration\EnvironmentAwareMigrationInterface;
use EonX\EasyDoctrine\Migration\Migration\ExpectedConnectionAwareMigrationInterface;

final readonly class MigrationFactory implements DoctrineMigrationFactory
{
    public function __construct(
        private MigrationFactory $decorated,
        private ManagerRegistry $managerRegistry,
        private string $environment,
    ) {
    }

    public function createVersion(string $migrationClassName): AbstractMigration
    {
        $migration = $this->decorated->createVersion($migrationClassName);

        if ($migration instanceof EnvironmentAwareMigrationInterface) {
            $migration->setEnvironment($this->environment);
        }

        if ($migration instanceof ExpectedConnectionAwareMigrationInterface) {
            $expectedConnectionName = $migration->getExpectedConnectionName();
            $expectedConnection = $this->managerRegistry->getConnection($expectedConnectionName);

            $migration->setExpectedConnection($expectedConnection);
        }

        return $migration;
    }
}
