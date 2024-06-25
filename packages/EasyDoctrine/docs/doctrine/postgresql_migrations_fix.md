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

```yaml
// services_dev.yaml
services:
    EonX\EasyDoctrine\Common\Listener\FixPostgreSqlDefaultSchemaListener:
        tags:
            - {name: doctrine.event_listener, event: postGenerateSchema}
```

[1]: https://github.com/doctrine/dbal/issues/1110
