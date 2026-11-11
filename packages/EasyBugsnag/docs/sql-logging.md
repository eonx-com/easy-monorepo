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

return App::config([
    'easy_bugsnag' => [
        'api_key' => env('BUGSNAG_API_KEY'),
        'doctrine_dbal' => true,
    ],
]);

```

You can also explicitly define the connections you want to log the queries for:

```php
# config/packages/easy_bugsnag.php

<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    'easy_bugsnag' => [
        'api_key' => env('BUGSNAG_API_KEY'),
        'doctrine_dbal' => [
            'connections' => [
                'default',
                'secure',
            ],
        ],
    ],
]);

```

You are now all set up to start logging SQL queries into your Bugsnag reports.

## Query parameter values

By default, the breadcrumbs do not contain the values of the query parameters. To add them, set the
`doctrine_dbal.log_query_values` configuration to `true`:

```php
# config/packages/easy_bugsnag.php

<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    'easy_bugsnag' => [
        'api_key' => env('BUGSNAG_API_KEY'),
        'doctrine_dbal' => [
            'log_query_values' => true,
        ],
    ],
]);

```

**Warning:** the values are sent to Bugsnag as is. Doctrine ORM uses positional parameters, which have no names, so
the sensitive data sanitizer cannot mask secrets or personal data in them. Values written into the SQL text are sent
as is in any case.
