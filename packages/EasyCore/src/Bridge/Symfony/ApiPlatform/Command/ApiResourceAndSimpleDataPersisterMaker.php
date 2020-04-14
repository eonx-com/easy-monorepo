<?php
declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\Command;

use ApiPlatform\Core\Bridge\Symfony\Bundle\ApiPlatformBundle;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

final class ApiResourceAndSimpleDataPersisterMaker extends AbstractMaker
{
    public static function getCommandName(): string
    {
        return 'make:simple_api_resource';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->setDescription('Create ApiResource + its SimpleDataPersister')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'The name of the ApiResource class (e.g. <fg=yellow>EwalletTransfer</>)'
            );
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
        $dependencies->addClassDependency(ApiPlatformBundle::class, 'api_platform');
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $resourceClassNameDetails = $generator->createClassNameDetails($input->getArgument('name'), 'Api\\Resource\\');
        $resourcePath = $generator->generateClass(
            $resourceClassNameDetails->getFullName(),
            __DIR__ . '/../../Resources/skeleton/api_resource.tpl.php',
            [
                'snakeCaseName' => Str::asSnakeCase($resourceClassNameDetails->getShortName())
            ]
        );

        $io->success(\sprintf('Generated %s', $resourcePath));
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
        if ($input->getArgument('name') !== null) {
            return;
        }

        $input->setArgument('name', $io->ask($command->getDefinition()->getArgument('name')->getDescription()));
    }
}
