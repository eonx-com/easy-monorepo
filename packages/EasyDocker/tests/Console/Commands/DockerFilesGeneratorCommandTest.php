<?php

declare(strict_types=1);

namespace EonX\EasyDocker\Tests\Console\Commands;

use EonX\EasyDocker\Tests\AbstractTestCase;

final class DockerFilesGeneratorCommandTest extends AbstractTestCase
{
    public function testDockerfileContainsPrestissimo(): void
    {
        $filesystem = $this->getFilesystem();
        $inputs = [
            'project',
            'false',
            'false',
            'false',
            // prestissimo
            'true',
        ];

        $this->executeCommand('generate', $inputs);

        self::assertTrue($filesystem->exists(static::$cwd . '/docker/api/Dockerfile'));
        self::assertStringContainsString(
            'composer global require hirak/prestissimo',
            (string)\file_get_contents(static::$cwd . '/docker/api/Dockerfile')
        );
    }

    public function testDockerfileDoesNotContainPrestissimo(): void
    {
        $filesystem = $this->getFilesystem();
        $inputs = [
            'project',
            'false',
            'false',
            'false',
            // prestissimo
            'false',
        ];

        $this->executeCommand('generate', $inputs);

        self::assertTrue($filesystem->exists(static::$cwd . '/docker/api/Dockerfile'));
        self::assertStringNotContainsString(
            'composer global require hirak/prestissimo',
            (string)\file_get_contents(static::$cwd . '/docker/api/Dockerfile')
        );
    }

    public function testEasyDirectoryBackwardsCompatibility(): void
    {
        $inputs = ['project', 'true', 'true', 'true', 'false'];

        $filesNotExisting = ['.easy/easy-docker-manifest.json', '.easy/easy-docker-params.yaml'];

        $this->getFilesystem()->dumpFile(static::$cwd . '/' . 'easy-docker-manifest.json', '{}');
        $this->getFilesystem()->touch(static::$cwd . '/' . 'easy-docker-params.yaml');

        $this->executeCommand('generate', $inputs);

        foreach ($filesNotExisting as $file) {
            self::assertFalse($this->getFilesystem()->exists(static::$cwd . '/' . $file));
        }
    }

    public function testGenerateDockerFiles(): void
    {
        $inputs = ['project', 'true', 'true', 'true', 'false'];

        $files = [
            '.easy/easy-docker-manifest.json',
            '.easy/easy-docker-params.yaml',
            'docker/api/cron/crontab',
            'docker/api/development/php.ini',
            'docker/api/development/php-composer.ini',
            'docker/api/Dockerfile',
            'docker/api/fpm.conf',
            'docker/api/migrate.sh',
            'docker/api/php.ini',
            'docker/api/startup.sh',
            'docker/nginx/snippets/cors.conf',
            'docker/nginx/default.conf',
            'docker/nginx/Dockerfile',
            'docker/nginx/nginx.conf',
            'docker/d.sh',
            'docker/da.sh',
            'docker/dc.sh',
            'docker/dm.sh',
            'docker/readme.md',
            'docker-compose.dev.yml',
            'docker-compose.local.yml',
            'docker-compose.yml',
        ];

        $this->executeCommand('generate', $inputs);

        foreach ($files as $file) {
            self::assertTrue($this->getFilesystem()->exists(static::$cwd . '/' . $file));
        }
    }
}
