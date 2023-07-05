<?php

declare(strict_types=1);

namespace EonX\EasySsm\Tests\Services\Dotenv\Loaders;

use EonX\EasySsm\Services\Dotenv\Data\EnvData;
use EonX\EasySsm\Services\Dotenv\Loaders\ConsoleOutputLoader;
use EonX\EasySsm\Tests\AbstractTestCase;
use Symfony\Component\Console\Output\StreamOutput;

final class ConsoleOutputLoaderTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testLoadEnv
     */
    public static function providerTestLoadEnv(): iterable
    {
        yield '1 env data' => [[new EnvData('env', 'value')], "export env='value';\n"];

        yield '2 env data' => [
            [new EnvData('env', 'value'), new EnvData('env2', 'value')],
            "export env='value';\nexport env2='value';\n",
        ];

        yield 'quoted env value' => [[new EnvData('env', "valu'e")], "export env='valu'\''e';\n"];

        yield 'multiline env value' => [
            [new EnvData('env', "value\nnewline\n")],
            "export env='value\nnewline\n';\n",
        ];
    }

    /**
     * @param \EonX\EasySsm\Services\Dotenv\Data\EnvData[] $envs
     *
     * @dataProvider providerTestLoadEnv
     */
    public function testLoadEnv(array $envs, string $expected): void
    {
        $output = new StreamOutput(\fopen('php://memory', 'w+'));

        (new ConsoleOutputLoader($output))->loadEnv($envs);

        \rewind($output->getStream());

        self::assertEquals($expected, \stream_get_contents($output->getStream()));
    }
}
