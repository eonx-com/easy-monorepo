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

use Symfony\Config\SecurityConfig;

return static function (SecurityConfig $security) : void {
    $someFirewall = $securityConfig->firewall('some_firewall_name');
    // Make sure you include `easy-security` in the pattern to protect your login form
    $someFirewall->pattern('^/(docs|easy-security)')
        ->security(true)
        ->provider('in_memory');

    $someFirewall->formLogin()
        ->checkPath('easy_security.login')
        ->enableCsrf(true)
        ->loginPath('easy_security.login');
};
```

### Configure CSRF protection

To protect your login form against CSRF attacks, you need to enable CSRF protection in your security configuration file.

```php
# src/config/packages/csrf.php
<?php
declare(strict_types=1);

use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $frameworkConfig): void {
    $frameworkConfig->form()
        ->csrfProtection()
        ->tokenId('submit');

    $frameworkConfig->csrfProtection()
        ->enabled(true)
        ->statelessTokenIds([
            'submit',
            'authenticate',
            'logout',
        ]);
};
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

### Configure controller

You need to register the `EasySecurityLoginController` in your services configuration file and pass the authentication success handler for your firewall.

```php
# src/config/services.php
<?php
<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasySecurity\Bundle\Controller\EasySecurityLoginController;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(EasySecurityLoginController::class)
        ->arg('$authenticationSuccessHandler', service('security.authentication.success_handler.<YOUR FIREWALL NAME>.form_login'));
};
```
