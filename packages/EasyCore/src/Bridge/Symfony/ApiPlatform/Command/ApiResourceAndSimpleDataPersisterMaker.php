<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\Command;

use ApiPlatform\Core\Bridge\Symfony\Bundle\ApiPlatformBundle as LegacyApiPlatformBundle;
use ApiPlatform\Symfony\Bundle\ApiPlatformBundle;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

final class ApiResourceAndSimpleDataPersisterMaker extends AbstractMaker
{
    public static function getCommandDescription(): string
    {
        return 'Create ApiResource + its SimpleDataPersister';
    }

    public static function getCommandName(): string
    {
        return 'make:simple_api_resource';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'The name of the ApiResource class (e.g. <fg=yellow>EwalletTransfer</>)',
            )
            ->addOption(
                'resource-namespace',
                null,
                InputOption::VALUE_OPTIONAL,
                'The namespace for the ApiResource class',
                'Api\\Resource\\',
            )
            ->addOption(
                'persister-namespace',
                null,
                InputOption::VALUE_OPTIONAL,
                'The namespace for the SimpleDataPersister class',
                'Api\\DataPersister\\',
            );
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
        // TODO: refactor in 5.0. Use the ApiPlatform\Symfony\Bundle\ApiPlatformBundle class only.
        $bundleClass = \class_exists(ApiPlatformBundle::class)
            ? ApiPlatformBundle::class
            : LegacyApiPlatformBundle::class;

        $dependencies->addClassDependency($bundleClass, 'api_platform');
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        /** @var string $name */
        $name = $input->getArgument('name');
        /** @var string $resourceNamespace */
        $resourceNamespace = $input->getOption('resource-namespace');
        /** @var string $persisterNamespace */
        $persisterNamespace = $input->getOption('persister-namespace');

        $resourceClassNameDetails = $generator->createClassNameDetails($name, $resourceNamespace);
        $resourceTpl = __DIR__ . '/../../Resources/skeleton/api_resource.tpl.php';
        $resourceVars = [
            'snakeCaseName' => Str::asSnakeCase($resourceClassNameDetails->getShortName()),
        ];

        $persisterName = \sprintf('%sPersister', $resourceClassNameDetails->getShortName());
        $persisterClassNameDetails = $generator->createClassNameDetails($persisterName, $persisterNamespace);
        $persisterTpl = __DIR__ . '/../../Resources/skeleton/simple_data_persister.tpl.php';
        $persisterVars = [
            'resourceFcqn' => $resourceClassNameDetails->getFullName(),
            'resourceShortName' => $resourceClassNameDetails->getShortName(),
        ];

        $generator->generateClass($resourceClassNameDetails->getFullName(), $resourceTpl, $resourceVars);
        $generator->generateClass($persisterClassNameDetails->getFullName(), $persisterTpl, $persisterVars);
        $generator->writeChanges();
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
        if ($input->getArgument('name') !== null) {
            return;
        }

        $input->setArgument('name', $io->ask($command->getDefinition()->getArgument('name')->getDescription()));
    }
}
