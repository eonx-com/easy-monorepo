---eonx_docs---
title: Symfony
weight: 1000
is_section: true
section_icon: fab fa-symfony
---eonx_docs---

### Register Bundle

If you're using [Symfony Flex][1], this step has been done automatically for you. If not, you can register the bundle
yourself:

```php
// config/bundles.php

return [
    // Other bundles ...

    EonX\EasyBugsnag\Bridge\Symfony\EasyBugsnagSymfonyBundle::class => ['all' => true],
];
```

<br>

### Configuration
The only required config is the api key from your Bugsnag organisation:

```yaml
# config/packages/easy_bugsnag.yaml

easy_bugsnag:
    api_key: '%env(BUGSNAG_API_KEY)%'
```

[1]: https://flex.symfony.com/
