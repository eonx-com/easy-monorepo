---eonx_docs---
title: Login form authentication
weight: 1001
---eonx_docs---

### Configure the firewall

In your security configuration file, define a firewall that uses the `easy_security_login_form` authenticator:

```php
src/config/packages/security.php
<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    'security' => [
        'firewalls' => [
            'some_firewall_name' => [
                // Make sure you include `easy-security` in the pattern to protect your login form
                'pattern' => '^/(docs|easy-security)',
                'security' => true,
                'provider' => 'in_memory',
                'form_login' => [
                    'check_path' => 'easy_security.login',
                    'enable_csrf' => true,
                    'login_path' => 'easy_security.login',
                ],
            ],
        ],
    ],
]);
```

### Configure CSRF protection

To protect your login form against CSRF attacks, you need to enable CSRF protection in your security configuration file.

```php
# src/config/packages/csrf.php
<?php
declare(strict_types=1);

return App::config([
    'framework' => [
        'form' => [
            'csrf_protection' => [
                'token_id' => 'submit',
            ],
        ],
        'csrf_protection' => [
            'stateless_token_ids' => [
                'submit',
                'authenticate',
                'logout',
            ],
        ],
    ],
]);
```

### Configure routes

The package provides routes for the login form. You need to import them in your routing configuration file.

```php
# src/config/routes/easy_security.php
<?php
declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routingConfigurator): void {
    $routingConfigurator->import('@EasySecurityBundle/config/routes.php');
};
```

### Configure package

You need to enable Login Form authentication in the package configuration file.

```php
# src/config/packages/easy_security.php
<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    'easy_security' => [
        'login_form' => [
            'firewall_name' => 'some_firewall_name',
        ],
    ],
]);

```
