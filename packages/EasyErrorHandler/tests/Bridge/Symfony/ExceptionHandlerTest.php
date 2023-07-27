<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Bridge\Symfony;

use EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface;
use Illuminate\Http\Request;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

final class ExceptionHandlerTest extends AbstractSymfonyTestCase
{
    /**
     * @dataProvider providerTestRenderWithDefaultBuilders
     */
    public function testRenderWithDefaultBuilders(
        Request $request,
        Throwable $exception,
        callable $assertResponse,
        ?array $config = null,
        ?array $translations = null,
    ): void {
        // Convert array config to yaml file for symfony to load
        if ($config !== null) {
            $config = [$this->dumpConfigFile($config)];
        }

        $container = $this->getKernel($config)
            ->getContainer();
        $handler = $container->get(ErrorHandlerInterface::class);

        if ($translations !== null) {
            /** @var \EonX\EasyErrorHandler\Tests\Bridge\Symfony\Stubs\TranslatorStub $translator */
            $translator = $container->get(TranslatorInterface::class);
            $translator->setTranslations($translations);
        }

        // Delete tmp config file
        if ($config !== null) {
            $this->cleanUpConfigFile($config);
        }

        $assertResponse($handler->render($request, $exception));
    }

    /**
     * @param string[] $files
     */
    private function cleanUpConfigFile(array $files): void
    {
        $filesystem = new Filesystem();

        foreach ($files as $filename) {
            $filesystem->remove($filename);
        }
    }

    private function dumpConfigFile(array $config): string
    {
        $filename = __DIR__ . '/tmp_config.yaml';

        $config = $config['easy-error-handler'];
        $config['verbose'] = $config['use_extended_response'] ?? false;

        unset($config['use_extended_response']);

        \file_put_contents($filename, Yaml::dump([
            'easy_error_handler' => $config,
        ]));

        return $filename;
    }
}
