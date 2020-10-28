---eonx_docs---
title: Introduction
weight: 0
---eonx_docs---

CLI tool to interact with AWS ParameterStore in a convenient way

<br>

### Require package (Composer)

We recommend to use [Composer][1] to manage your dependencies. You can require this package as follows:

```bash
$ composer require eonx-com/easy-ssm
```

<br>

### Commands

- **apply:** Apply local changes to remote AWS ParameterStore
- **diff:** Resolve diff between local parameters and AWS ParameterStore
- **dump-envs:** Dump env vars in a PHP file to improve loading time
- **export-envs:** Fetch parameters from AWS ParameterStore and output shell syntax to export them as env variables
- **init:** Initialise local parameters from AWS ParameterStore
- **local-apply:** Apply changes locally when "*_old" file has been generated
- **local-diff:** Resolve diff locally when "*_old" file has been generated

<br>

### Dotenv integration

If working on a PHP project, you can use the Dotenv integration provided by this package to load your AWS ParameterStore
parameters as env variables within PHP:

```php
// bootstrap.php

require_once __DIR__ . '/vendor/autoload.php';

// Will fetch parameters from AWS ParameterStore and export them as env vars
(new \EonX\EasySsm\Dotenv\SsmDotenv())->loadEnv();

// Now your parameters from AWS ParameterStore are available in: $_ENV, $_SERVER and via \getenv()
```

[1]: https://getcomposer.org/
