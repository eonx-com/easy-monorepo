---eonx_docs---
title: PostgreSQL migrations fix
weight: 3000
is_section: true
---eonx_docs---

### Issue description

When using PostgreSQL, `$this->addSql('CREATE SCHEMA public')` is automatically added to all the newly created migration files.

[Issue on GitHub][1]

### Enable fix

#### Symfony

Register the listener:

```php
# services_dev.php or services_local.php

<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyDoctrine\Common\Listener\FixPostgreSqlDefaultSchemaListener;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Workaround for an issue: https://github.com/doctrine/dbal/issues/1110
    $services->set(FixPostgreSqlDefaultSchemaListener::class)
        ->tag('doctrine.event_listener', ['event' => 'postGenerateSchema']);
};

```

[1]: https://github.com/doctrine/dbal/issues/1110
