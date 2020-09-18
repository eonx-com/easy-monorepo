---eonx_docs---
title: PostgreSQL migrations fix
weight: 3000
is_section: true
---eonx_docs---

### Issue description

During create a migration the `down()` method always has: `$this->addSql('CREATE SCHEMA public');`

[Issue on GitHub][1]

### Enable fix

Register listener
```yaml
// services_dev.yaml
services:
    \EonX\EasyCore\Doctrine\Events\FixPostgreSqlDefaultSchemaListener:
        tags:
            - { name: doctrine.event_listener, event: postGenerateSchema }
```


[1]: https://github.com/doctrine/dbal/issues/1110
