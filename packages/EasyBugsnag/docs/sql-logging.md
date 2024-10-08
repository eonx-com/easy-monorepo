---eonx_docs---
title: 'SQL query logging'
weight: 1006
---eonx_docs---

# SQL query logging

Since having the list of SQL queries executed during a request that triggered an error or exception can make debugging
much easier, the EasyBugsnag package provides logging of SQL queries for Bugsnag. The SQL queries are shown on the
*Breadcrumbs* tab of Bugsnag.

## Symfony

To add SQL queries details to your Bugsnag reports in Symfony, simply set the `doctrine_dbal.enabled` configuration to
`true`:

```php
# config/packages/easy_bugsnag.php

<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\EasyBugsnagConfig;

return static function (EasyBugsnagConfig $easyBugsnagConfig): void {
    $easyBugsnagConfig
        ->apiKey(env('BUGSNAG_API_KEY'));

    $doctrineDbal = $easyBugsnagConfig->doctrineDbal();
    $doctrineDbal
        ->enabled(true);
};

```

You can also explicitly define the connections you want to log the queries for:

```php
# config/packages/easy_bugsnag.php

<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\EasyBugsnagConfig;

return static function (EasyBugsnagConfig $easyBugsnagConfig): void {
    $easyBugsnagConfig
        ->apiKey(env('BUGSNAG_API_KEY'));

    $doctrineDbal = $easyBugsnagConfig->doctrineDbal();
    $doctrineDbal
        ->enabled(true)
        ->connections([
            'default',
            'secure',
        ]);
};

```

You are now all set up to start logging SQL queries into your Bugsnag reports.
