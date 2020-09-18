<?php

declare(strict_types=1);

namespace EonX\EasyCfhighlander\Tests\Console\Commands;

use EonX\EasyCfhighlander\Tests\AbstractTestCase;

final class CodeCommandTest extends AbstractTestCase
{
    public function testEasyDirectoryBackwardsCompatibility(): void
    {
        $inputs = [
            // project
            'project',
            // db_name
            'projectDatabase',
            // db_username
            'projectDatabaseUsername',
            // dns_domain
            'project.com',
            // redis_enabled,
            'true',
            // elasticsearch_enabled
            'true',
            // ssm_prefix
            'project',
            // sqs_queue
            'project',
            // dev_account
            'aws_dev_account',
            // ops_account
            '599070804856',
            // prod_account
            'aws_prod_account',
            // cli_enabled
            'true',
        ];

        $filesNotExisting = ['.easy/easy-cfhighlander-manifest.json', '.easy/easy-cfhighlander-params.yaml'];

        $this->getFilesystem()->dumpFile(static::$cwd . '/' . 'easy-cfhighlander-manifest.json', '{}');
        $this->getFilesystem()->touch(static::$cwd . '/' . 'easy-cfhighlander-params.yaml');

        $this->executeCommand('code', $inputs);

        foreach ($filesNotExisting as $file) {
            self::assertFalse($this->getFilesystem()->exists(static::$cwd . '/' . $file));
        }
    }

    public function testGenerateCloudFormationFiles(): void
    {
        $inputs = [
            // project
            'project',
            // db_name
            'projectDatabase',
            // db_username
            'projectDatabaseUsername',
            // dns_domain
            'project.com',
            // redis_enabled,
            'true',
            // elasticsearch_enabled
            'true',
            // ssm_prefix
            'project',
            // sqs_queue
            'project',
            // dev_account
            'aws_dev_account',
            // ops_account
            '599070804856',
            // prod_account
            'aws_prod_account',
            // cli_enabled
            'true',
        ];

        $files = [
            'Jenkinsfile',
            'project.cfhighlander.rb',
            'project.config.yaml',
            'project-schema.cfhighlander.rb',
            'project-schema.config.yaml',
        ];

        $display = $this->executeCommand('code', $inputs);

        self::assertStringContainsString(\sprintf('Generating files in %s:', \realpath(static::$cwd)), $display);

        foreach ($files as $file) {
            self::assertTrue($this->getFilesystem()->exists(static::$cwd . '/' . $file));
        }
    }
}
