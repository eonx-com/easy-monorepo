<?php

declare(strict_types=1);

namespace EonX\EasySsm\Tests\Console\Commands;

use EonX\EasySsm\Tests\AbstractTestCase;

final class ExportEnvsCommandTest extends AbstractTestCase
{
    public function testExportEnvs(): void
    {
        $output = $this->executeCommand('export-envs', null, [
            __DIR__ . '/../../../config/console_loader.yaml',
            __DIR__ . '/../../Fixtures/Config/stub_ssm_client.yaml',
        ]);

        // TODO - Have a look at getting the output from container
        self::assertEmpty($output);
    }
}
