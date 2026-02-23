---eonx_docs---
title: Simplify your security expressions
weight: 1001
---eonx_docs---

The [Security Component][1] offers the ability to define complex security strategies by using the [expressions][2] based
on the [ExpressionLanguage Component][3].

This package recommends defining your permissions using public constants on an interface as:

```php
namespace App\Security\Interfaces;

interface PermissionsInterface
{
    public const PERMISSION_OBJECT_CREATE = 'object:create';
}
```

Based on the example provided by the [Symfony documentation][2], creating an expression to check if the user is granted
our permissions we would have to do something like that:

```php
use Symfony\Component\ExpressionLanguage\Expression;
// ...

public function index()
{
    $this->denyAccessUnlessGranted(new Expression(
        "is_granted(constant('\\App\\Security\\Interfaces\\PermissionInterface::PERMISSION_OBJECT_CREATE'), object)"
    ));

    // ...
}
```

It works fine, but it requires you to write the fully qualified name of the constant each time and when used as part
of annotations (e.g. on an ApiResource from ApiPlatform) it can break your coding standards because the line is too long...

<br>

To simplify all that, this package provides an expression function to help us to use our permissions within expressions.

### Define your permissions locations

In the config, define your permissions locations by providing a list of the classes/interfaces where your permissions
are defined:

```php
# config/packages/easy_security.php

<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Infrastructure\EasySecurity\Authorization\PermissionsInterface;
use App\Infrastructure\EasySecurity\Authorization\RolesInterface;

return App::config([
    'easy_security' => [
        'permissions_locations' => [
            PermissionsInterface::class,
        ],
        'roles_locations' => [
            RolesInterface::class,
        ],
    ],
]);

```

The package will now know where to look for your permissions.

### Use the function in your expressions

Once the configuration is defined, we just have to use the `permission` expression function in our expressions and only
give it the name of the constant:

```diff
use Symfony\Component\ExpressionLanguage\Expression;
// ...

public function index()
{
    $this->denyAccessUnlessGranted(new Expression(
-        "is_granted(constant('\\App\\Security\\Interfaces\\PermissionInterface::PERMISSION_OBJECT_CREATE'), object)"
+        "is_granted(permission('PERMISSION_OBJECT_CREATE'), object)"
    ));

    // ...
}
```

[1]: https://symfony.com/doc/current/components/security.html

[2]: https://symfony.com/doc/current/security/expressions.html

[3]: https://symfony.com/doc/current/components/expression_language.html
