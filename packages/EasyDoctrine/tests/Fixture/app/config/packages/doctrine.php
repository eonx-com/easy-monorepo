<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\DBAL\Types\Types;
use EonX\EasyDoctrine\Common\Function\Cast;
use EonX\EasyDoctrine\Common\Function\Contains;
use EonX\EasyDoctrine\Common\Function\StringAgg;
use EonX\EasyDoctrine\Common\Type\CarbonImmutableDateTimeMicrosecondsType;
use EonX\EasyDoctrine\Tests\Fixture\App\Type\PriceType;
use Symfony\Config\DoctrineConfig;

return static function (DoctrineConfig $doctrineConfig): void {
    $dbal = $doctrineConfig->dbal();

    $dbal->type(PriceType::NAME)
        ->class(PriceType::class);

    $dbal->type(Types::DATETIME_IMMUTABLE)
        ->class(CarbonImmutableDateTimeMicrosecondsType::class);

    $connection = $dbal->connection('default');
    $connection
        ->useSavepoints(true)
        ->driver('pdo_sqlite')
        ->url('sqlite:///:memory:');

    $doctrineConfig->orm()
        ->autoGenerateProxyClasses(true);

    $entityManager = $doctrineConfig->orm()
        ->entityManager('default');

    $entityManager->dql()
        ->stringFunction('CAST', Cast::class)
        ->stringFunction('CONTAINS', Contains::class)
        ->stringFunction('STRING_AGG', StringAgg::class);

    $entityManager
        ->namingStrategy('doctrine.orm.naming_strategy.underscore_number_aware');

    $entityManager->mapping('AppEntity')
        ->dir(param('kernel.project_dir') . '/src/Entity')
        ->isBundle(false)
        ->prefix('EonX\EasyDoctrine\Tests\Fixture\App\Entity')
        ->type('attribute');
};
