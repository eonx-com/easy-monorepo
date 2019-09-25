<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyCore\Bridge\Laravel;

use Illuminate\Support\ServiceProvider;

class ConfigurationServiceProvider extends ServiceProvider
{
    /**
     * The config file pattern.
     *
     * @var string
     */
    private const CONFIG_FILE_REGEXP = '/^.*\.php$/';

    /**
     * The config folder name.
     *
     * @var string
     */
    private const CONFIG_FOLDER_NAME = 'config';

    /** @noinspection PhpMissingParentCallCommonInspection */
    /**
     * Register all the application config files.
     *
     * @return void
     */
    public function register(): void
    {
        $configPath = \implode(\DIRECTORY_SEPARATOR, [
            $this->app->basePath(),
            self::CONFIG_FOLDER_NAME
        ]);
        if (\is_dir($configPath) === false) {
            return;
        }
        /** @var \Laravel\Lumen\Application $app */
        $app = $this->app;
        // glob() directly calls into libc glob(), which is not aware of PHP
        // stream wrappers. Same for \GlobIterator (which additionally requires
        // an absolute realpath() on Windows).
        // @see https://github.com/mikey179/vfsStream/issues/2
        $files = \scandir($configPath);
        foreach ($files as $configFile) {
            if (\preg_match(self::CONFIG_FILE_REGEXP, $configFile) &&
                \is_file(\implode(\DIRECTORY_SEPARATOR, [
                    $configPath,
                    $configFile
                ]))
            ) {
                $app->configure(\str_replace('.php', '', \basename($configFile)));
            }
        }
    }
}
