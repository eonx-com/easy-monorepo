<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Common\Listener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\DBAL\Schema\PostgreSQLSchemaManager;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Doctrine\ORM\Tools\ToolEvents;

/**
 * Workaround for an issue: https://github.com/doctrine/dbal/issues/1110.
 *
 * @author https://github.com/vudaltsov
 */
#[AsDoctrineListener(event: ToolEvents::postGenerateSchema)]
final class FixPostgreSqlDefaultSchemaListener
{
    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function postGenerateSchema(GenerateSchemaEventArgs $args): void
    {
        $schemaManager = $args
            ->getEntityManager()
            ->getConnection()
            ->createSchemaManager();

        if ($schemaManager instanceof PostgreSQLSchemaManager === false) {
            return;
        }

        foreach ($schemaManager->getExistingSchemaSearchPaths() as $namespace) {
            if ($args->getSchema()->hasNamespace($namespace) === false) {
                $args->getSchema()
                    ->createNamespace($namespace);
            }
        }
    }
}
