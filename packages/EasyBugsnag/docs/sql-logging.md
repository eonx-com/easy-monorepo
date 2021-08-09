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

```yaml
# config/packages/easy_bugsnag.yaml

easy_bugsnag:
    api_key: '%env(BUGSNAG_API_KEY)%'
    doctrine_dbal:
        enabled: true
```

You can also explicitly define the connections you want to log the queries for:

```yaml
# config/packages/easy_bugsnag.yaml

easy_bugsnag:
    api_key: '%env(BUGSNAG_API_KEY)%'

    doctrine_dbal:
        enabled: true
        connections:
            - default
            - secure
```

You are now all set up to start logging SQL queries into your Bugsnag reports.

## Laravel/Lumen

To add SQL queries details to your Bugsnag reports if your application is using Laravel/Lumen and
[Laravel Doctrine ORM][1], simply set the `doctrine_orm` configuration to `true`:

```php
// config/easy-bugsnag.php

return [
    'api_key' => \env('BUGSNAG_API_KEY'),
    'doctrine_orm' => true,
];
```

You must also register the SQL Logger from EasyBugsnag into the Doctrine configuration:

```php
// config/doctrine.php

return [
    // ...

    'logger' => EonX\EasyBugsnag\Bridge\Laravel\Doctrine\SqlOrmLogger::class,

    // ...
];
```

[1]: http://www.laraveldoctrine.org/docs/1.4/orm
