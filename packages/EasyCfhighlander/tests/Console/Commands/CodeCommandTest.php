<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyCfhighlander\Tests\Console\Commands;

use LoyaltyCorp\EasyCfhighlander\Tests\AbstractTestCase;

final class CodeCommandTest extends AbstractTestCase
{
    /**
     * Command should generate cloudformation files.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function testGenerateCloudFormationFiles(): void
    {
        $inputs = [
            'project', // project
            'projectDatabase', // db_name
            'projectDatabaseUsername', // db_username
            'project.com', // dns_domain
            'true', // redis_enabled,
            'true', // elasticsearch_enabled
            'project', // ssm_prefix
            'project', // sqs_queue
            'aws_dev_account', // dev_account
            '599070804856', // ops_account
            'aws_prod_account' // prod_account
        ];

        $files = [
            'Jenkinsfile',
            'project.cfhighlander.rb',
            'project.config.yaml',
            'project-schema.cfhighlander.rb',
            'project-schema.config.yaml'
        ];

        $display = $this->executeCommand('code', $inputs);

        self::assertContains(\sprintf('Generating files in %s:', \realpath(static::$cwd)), $display);

        foreach ($files as $file) {
            self::assertTrue($this->getFilesystem()->exists(static::$cwd . '/' . $file));
        }
    }
}
