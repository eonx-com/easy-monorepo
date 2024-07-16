---eonx_docs---
title: 'Configuration'
weight: 1007
---eonx_docs---

# Configuration

You can configure global settings for the EasyBugsnag package via a configuration file in your application.

::: tip
The only configuration required is the Bugsnag Integration API Key for your project.
:::

## Configuration files

For Laravel applications, the EasyBugsnag configuration file must be called `easy-bugsnag.php` and be located in the
`config` directory.

For Symfony applications, the EasyBugsnag configuration file can be a YAML, XML or PHP file located under the
`config/packages` directory, with a name like `easy_bugsnag.<format>`. The root node of the configuration must be called
`easy_bugsnag`.

## Configuration options

The common configuration options for Laravel and Symfony are as follows:

| Configuration                             | Default                                                                                                                           | Description                                                                                                     |
|-------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------|-----------------------------------------------------------------------------------------------------------------|
| `enabled`                                 | `true`                                                                                                                            | Enable/disable the entire EasyBugsnag package.                                                                  |
| `api_key`                                 | N/A                                                                                                                               | Bugsnag Integration API Key of your project.                                                                    |
| `project_root`                            | `%kernel.project_dir%/src` (Symfony)<br/>`\base_path('app')` (Laravel)                                                            | Project root.                                                                                                   |
| `release_stage`                           | `%env(APP_ENV)%` (Symfony)<br/>`\env('APP_ENV')` (Laravel)                                                                        | Release stage.                                                                                                  |
| `strip_path`                              | `%kernel.project_dir%` (Symfony)<br/>`\base_path()` (Laravel)                                                                     | Strip path.                                                                                                     |
| `app_name.enabled`                        | `false`                                                                                                                           | Enable/disable APP name data in Bugsnag.                                                                        |
| `app_name.env_var`                        | `APP_NAME`                                                                                                                        | Env var used by default APP name resolver.                                                                      |
| `aws_ecs_fargate.enabled`                 | `false`                                                                                                                           | Enable/disable AWS ECS Fargate data in Bugsnag. See [AWS ECS Fargate information](aws.md) for more information. |
| `aws_ecs_fargate.meta_url`                | `%env(ECS_CONTAINER_METADATA_URI_V4)%/task` (Symfony)<br/> `\sprintf('%s/task', \env('ECS_CONTAINER_METADATA_URI_V4'))` (Laravel) | URL used to fetch AWS ECS Fargate task metadata.                                                                |
| `aws_ecs_fargate.meta_storage_filename`   | `%kernel.cache_dir%/aws_ecs_fargate_meta.json` (Symfony)<br/> `\storage_path('aws_ecs_fargate_meta.json')` (Laravel)              | Filename to cache AWS ECS Fargate task metadata into.                                                           |
| `session_tracking.enabled`                | `false`                                                                                                                           | Enable session tracking. See [Session tracking](session-tracking.md) for more information.                      |
| `session_tracking.cache_expires_after`    | `3600`                                                                                                                            | Expiry for sessions cache in seconds.                                                                           |
| `session_tracking.exclude_urls`           | `[]`                                                                                                                              | List of URLs (or regular expression) to exclude from session tracking.                                          |
| `session_tracking.exclude_urls_delimiter` | `#`                                                                                                                               | Delimiter used in regular expression to resolve excluded URLs.                                                  |
| `use_default_configurators`               | `true`                                                                                                                            | Enable/disable the default configurators.                                                                       |

Laravel has the following additional configuration option:

| Configuration                                   | Default | Description                                                                   |
|-------------------------------------------------|---------|-------------------------------------------------------------------------------|
| `session_tracking.cache_store`                  | `file`  | Cache store used by the default cache implementation provided by the package. |
| `session_tracking.queue_job_count_for_sessions` | `false` | Enable/disable session tracking for queue jobs.                               |

Symfony has the following additional configuration options:

| Configuration                                           | Default                 | Description                                                                                                       |
|---------------------------------------------------------|-------------------------|-------------------------------------------------------------------------------------------------------------------|
| `runtime`                                               | `symfony`               | Set the Symfony runtime.                                                                                          |
| `runtime_version`                                       | `Kernel::VERSION`       | Set the Symfony runtime version.                                                                                  |
| `doctrine_dbal.enabled`                                 | `true`                  | Enable SQL query logging (see [SQL query logging](sql-logging.md)).                                               |
| `doctrine_dbal.connections`                             | `['default']`           | Connections to log SQL queries for.                                                                               |
| `session_tracking.cache_directory`                      | `%kernel.cache_dir%`    | Directory used by the default cache implementation provided by the package.                                       |
| `session_tracking.cache_namespace`                      | `easy_bugsnag_sessions` | Namespace used by the default cache implementation provided by the package.                                       |
| `session_tracking.messenger_message_count_for_sessions` | `false`                 | Enable/disable session tracking for messenger messages.                                                           |
| `worker_info.enabled`                                   | `false`                 | Enable/disable worker information data in Bugsnag. See [Worker information](worker-info.md) for more information. |

## Example configuration files

### Symfony

In Symfony, you could have a configuration file called `easy_bugsnag.php` that looks like the following:

```php
<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Config\EasyBugsnagConfig;

return static function (EasyBugsnagConfig $easyBugsnagConfig): void {
    $easyBugsnagConfig
        ->enabled(true)
        ->apiKey(env('BUGSNAG_API_KEY'))
        ->projectRoot(param('kernel.project_dir') . '/src')
        ->releaseStage(env('APP_ENV'))
        ->stripPath(param('kernel.project_dir'))
        ->runtime('symfony')
        ->runtimeVersion(Kernel::VERSION);

    $awsEcsFargate = $easyBugsnagConfig->awsEcsFargate();
    $awsEcsFargate
        ->enabled(true)
        ->metaUrl(env('ECS_CONTAINER_METADATA_URI_V4') . '/task')
        ->metaStorageFilename(param('kernel.cache_dir') . '/aws_ecs_fargate_meta.json');

    $doctrineDbal = $easyBugsnagConfig->doctrineDbal();
    $doctrineDbal
        ->enabled(true)
        ->connections([
            'default',
        ]);

    $sessionTracking = $easyBugsnagConfig->sessionTracking();
    $sessionTracking
        ->enabled(true)
        ->cacheDirectory(param('kernel.cache_dir'))
        ->cacheExpiresAfter(3600)
        ->cacheNamespace('easy_bugsnag_sessions')
        ->excludeUrls([])
        ->excludeUrlsDelimiter('#')
        ->messengerMessageCountForSessions(false);

    $easyBugsnagConfig->workerInfo()
        ->enabled(true);

    $easyBugsnagConfig->useDefaultConfigurators(true);
};

```

### Laravel

In Laravel, the `easy-bugsnag.php` configuration file could look like the following:

``` php
<?php
declare(strict_types=1);

return [
    'enabled' => true,
    'api_key' => \env('BUGSNAG_API_KEY'),
    'project_root' => \base_path('app'),
    'release_stage' => \env('APP_ENV'),
    'strip_path' => \base_path(),
    'aws_ecs_fargate' => [
        'enabled' => true,
        'meta_url' => \sprintf('%s/task', \env('ECS_CONTAINER_METADATA_URI_V4')),
        'meta_storage_filename' => \storage_path('aws_ecs_fargate_meta.json'),
    ],
    'session_tracking' => [
        'enabled' => true,
        'cache_expires_after' => 3600,
        'cache_store' => 'file',
        'exclude_urls' => [],
        'exclude_urls_delimiter' => '#',
        'queue_job_count_for_sessions' => false,
    ],
    'use_default_configurators' => true,
];
```
