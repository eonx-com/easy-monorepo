<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Tests\Unit\src\Common\Runtime;

use EonX\EasySwoole\Common\Enum\SwooleRunnerEvent;
use EonX\EasySwoole\Common\Helper\OptionHelper;
use EonX\EasySwoole\Common\Runtime\EasySwooleRuntime;
use EonX\EasySwoole\Tests\Stub\Kernel\FrameworkKernelStub;
use EonX\EasySwoole\Tests\Stub\Kernel\KernelStub;
use EonX\EasySwoole\Tests\Unit\AbstractUnitTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class EasySwooleRuntimeTest extends AbstractUnitTestCase
{
    private const string APP_RUNTIME_MODE = 'APP_RUNTIME_MODE';

    private array $envBackup = [];

    private string|false $runtimeModeEnvVarBackup = false;

    private array $serverBackup = [];

    private string $varDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->envBackup = $_ENV;
        $this->serverBackup = $_SERVER;
        $this->runtimeModeEnvVarBackup = \getenv(self::APP_RUNTIME_MODE);
        $this->varDir = \sys_get_temp_dir() . '/easy-swoole-runtime-test-' . \bin2hex(\random_bytes(4));

        // The host or CI may export APP_RUNTIME_MODE
        \putenv(self::APP_RUNTIME_MODE);
        unset($_ENV[self::APP_RUNTIME_MODE], $_SERVER[self::APP_RUNTIME_MODE]);
    }

    protected function tearDown(): void
    {
        // The runtime also writes Swoole options and the EasyBugsnag server param in getRunner()
        $_ENV = $this->envBackup;
        $_SERVER = $this->serverBackup;
        \putenv(
            $this->runtimeModeEnvVarBackup === false
                ? self::APP_RUNTIME_MODE
                : \sprintf('%s=%s', self::APP_RUNTIME_MODE, $this->runtimeModeEnvVarBackup)
        );
        OptionHelper::setOptions([]);
        new Filesystem()
            ->remove($this->varDir);

        parent::tearDown();
    }

    public function testGetRunnerDoesNotOverrideRuntimeModeSetInEnv(): void
    {
        $_ENV[self::APP_RUNTIME_MODE] = 'custom=1';
        $runtime = new EasySwooleRuntime([
            'disable_dotenv' => true,
            'error_handler' => false,
        ]);

        $runtime->getRunner(new KernelStub());

        self::assertSame('custom=1', $_ENV[self::APP_RUNTIME_MODE]);
        self::assertArrayNotHasKey(self::APP_RUNTIME_MODE, $_SERVER);
    }

    public function testGetRunnerDoesNotOverrideRuntimeModeSetInServer(): void
    {
        $_SERVER[self::APP_RUNTIME_MODE] = 'custom=1';
        $runtime = new EasySwooleRuntime([
            'disable_dotenv' => true,
            'error_handler' => false,
        ]);

        $runtime->getRunner(new KernelStub());

        self::assertSame('custom=1', $_SERVER[self::APP_RUNTIME_MODE]);
        self::assertArrayNotHasKey(self::APP_RUNTIME_MODE, $_ENV);
    }

    public function testGetRunnerDoesNotOverrideRuntimeModeSetViaPutenv(): void
    {
        \putenv('APP_RUNTIME_MODE=custom=1');
        $runtime = new EasySwooleRuntime([
            'disable_dotenv' => true,
            'error_handler' => false,
        ]);

        $runtime->getRunner(new KernelStub());

        self::assertSame('custom=1', \getenv(self::APP_RUNTIME_MODE));
        self::assertArrayNotHasKey(self::APP_RUNTIME_MODE, $_ENV);
        self::assertArrayNotHasKey(self::APP_RUNTIME_MODE, $_SERVER);
    }

    public function testGetRunnerDoesNotSetRuntimeModeForConsoleApplication(): void
    {
        $runtime = new EasySwooleRuntime([
            'disable_dotenv' => true,
            'error_handler' => false,
        ]);

        $runtime->getRunner(new Application());

        self::assertFalse(\getenv(self::APP_RUNTIME_MODE));
        self::assertArrayNotHasKey(self::APP_RUNTIME_MODE, $_ENV);
        self::assertArrayNotHasKey(self::APP_RUNTIME_MODE, $_SERVER);
    }

    public function testGetRunnerMakesHtmlErrorsUseHtmlRendererAfterCacheWarmup(): void
    {
        $kernel = new FrameworkKernelStub($this->varDir);
        $runtime = new EasySwooleRuntime([
            'app_cache_warmup_enabled' => true,
            'disable_dotenv' => true,
            'error_handler' => false,
        ]);

        $runtime->getRunner($kernel);
        $response = $kernel->handle(
            Request::create('/nonexistent-path', Request::METHOD_GET, server: ['HTTP_ACCEPT' => 'text/html'])
        );

        self::assertSame(404, $response->getStatusCode());
        self::assertStringContainsString('The server returned a "404 Not Found".', (string) $response->getContent());
        self::assertStringNotContainsString(NotFoundHttpException::class, (string) $response->getContent());
    }

    public function testGetRunnerSetsHttpRuntimeModeBeforeEnvVarsLoadedCallback(): void
    {
        $runtimeModeInCallback = null;
        $runtime = new EasySwooleRuntime([
            'callbacks' => [
                SwooleRunnerEvent::EnvVarsLoaded->value => static function (array $server) use (
                    &$runtimeModeInCallback,
                ): void {
                    $runtimeModeInCallback = $server[self::APP_RUNTIME_MODE] ?? null;
                },
            ],
            'disable_dotenv' => true,
            'error_handler' => false,
        ]);

        $runtime->getRunner(new KernelStub());

        self::assertSame('web=1&worker=1', $runtimeModeInCallback);
        self::assertSame('web=1&worker=1', \getenv(self::APP_RUNTIME_MODE));
        self::assertSame('web=1&worker=1', $_ENV[self::APP_RUNTIME_MODE]);
        self::assertSame('web=1&worker=1', $_SERVER[self::APP_RUNTIME_MODE]);
    }

    public function testGetRunnerSetsHttpRuntimeModeWhenRuntimeModeIsEmpty(): void
    {
        $_ENV[self::APP_RUNTIME_MODE] = '';
        $_SERVER[self::APP_RUNTIME_MODE] = '';
        $runtime = new EasySwooleRuntime([
            'disable_dotenv' => true,
            'error_handler' => false,
        ]);

        $runtime->getRunner(new KernelStub());

        self::assertSame('web=1&worker=1', $_ENV[self::APP_RUNTIME_MODE]);
        self::assertSame('web=1&worker=1', $_SERVER[self::APP_RUNTIME_MODE]);
    }
}
