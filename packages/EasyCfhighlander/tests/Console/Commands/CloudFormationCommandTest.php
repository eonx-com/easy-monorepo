<?php
declare(strict_types=1);

namespace EonX\EasyCfhighlander\Tests\Console\Commands;

use EonX\EasyCfhighlander\Tests\AbstractTestCase;

final class CloudFormationCommandTest extends AbstractTestCase
{
    public function testEasyDirectoryBackwardsCompatibility(): void
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
            'aws_prod_account', // prod_account
            'true' // cli_enabled
        ];

        $filesNotExisting = [
            '.easy/easy-cfhighlander-manifest.json',
            '.easy/easy-cfhighlander-params.yaml'
        ];

        $this->getFilesystem()->dumpFile(static::$cwd . '/' . 'easy-cfhighlander-manifest.json', '{}');
        $this->getFilesystem()->touch(static::$cwd . '/' . 'easy-cfhighlander-params.yaml');

        $this->executeCommand('code', $inputs);

        foreach ($filesNotExisting as $file) {
            self::assertFalse(
                $this->getFilesystem()->exists(static::$cwd . '/' . $file),
                \sprintf('%s exists, but was not expected to', $file)
            );
        }
    }

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
            'aws_prod_account', // prod_account
            'true' // cli_enabled
        ];

        $files = [
            'project-backend.cfhighlander.rb',
            'project-backend.config.yaml',
            'project-backend.mappings.yaml',
            'Jenkinsfile',
            'aurora.config.yaml',
            'az.mappings.yaml',
            'bastion.config.yaml',
            'ecs.config.yaml',
            'elasticsearch.config.yaml',
            'kms.config.yaml',
            'loadbalancer.config.yaml',
            'redis.config.yaml',
            'sqs.config.yaml',
            'vpc.config.yaml',
            'redis/redis.cfhighlander.rb',
            'redis/redis.cfndsl.rb',
            'redis/redis.config.yaml',
            'redis/redis.mappings.yaml'
        ];

        $display = $this->executeCommand('cloudformation', $inputs);

        self::assertStringContainsString(\sprintf('Generating files in %s:', \realpath(static::$cwd)), $display);

        foreach ($files as $file) {
            self::assertTrue($this->getFilesystem()->exists(static::$cwd . '/' . $file));
        }
    }
}
