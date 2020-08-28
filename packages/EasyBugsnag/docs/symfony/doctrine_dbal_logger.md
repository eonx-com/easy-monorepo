---eonx_docs---
title: Doctrine ORM SQL Logger In Symfony
weight: 1002
---eonx_docs---

To add SQL queries details to your Bugsnag reports in Symfony, you will need to update the configuration to enable this
feature:

```yaml
# config/packages/easy_bugsnag.yaml

easy_bugsnag:
    api_key: '%env(BUGSNAG_API_KEY)%'

    doctrine_dbal: true
```

<br>

Additionally, you can explicitly define the connections you want to log the queries for:

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

That's it! Yes, for real... You are all setup to start logging SQL queries into your reports.
