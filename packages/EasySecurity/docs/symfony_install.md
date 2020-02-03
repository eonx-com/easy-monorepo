<div align="center">
    <h1>EonX - EasySecurity</h1>
    <p>Provides security features to be generic across applications.</p>
</div>

---

This document describes the steps to install this package in a [Symfony][1] application.

# Dependencies

This package has dependencies on the following packages, please see their documentation directly:
- [EasyApiToken][3]
- [EasyPsr7Factory][4]

# Require package (Composer)

Laravel uses [Composer][3] to manage its dependencies. You can require this package as following:

```bash
$ composer require eonx/easy-security
```

# Register Bundle

If your application doesn't use Symfony Flex then you will have to register the bundle yourself:

```php
// config/bundles.php

return [
    // Other bundles ...
    
    EonX\EasySecurity\Bridge\Symfony\EasySecurityBundle::class => ['all' => true]
];
```

# Getting Started

## Create Context and ContextFactory

This package provides you built-in features to resolve a security context specific to your application, to do so it
needs to know about the Context class and how to create it (via your ContextFactory).

### Context

You application MUST define a Context class (you can name it whatever you want but come on.. let's be consistent!).
This class MUST somehow implement `Eonx\EasySecurity\Interfaces\ContextInterface`. This package provides a base `Context`
class and here are our recommendation in term of implementation:

- Create a `ContextInterface` interface which extends `Eonx\EasySecurity\Interfaces\ContextInterface`
- Create a `Context` class which extends `Eonx\EasySecurity\Context` and implements your `ContextInterface`

This way when you migrate to PHP7.4, you will be able to use [Covariance][5]!

```php
// src/Security/Interfaces/ContextInterface.php
namespace App\Security;

use Eonx\EasySecurity\Interfaces\ContextInterface as BaseContextInterface;

interface ContextInterface extends BaseContextInterface 
{
    // Override methods and return types here...
}

// src/Security/Context.php
namespace App\Security;

use App\Security\ContextInterface;
use Eonx\EasySecurity\Context as BaseContext;

final class Context extends BaseContext implements ContextInterface
{
    // Override methods and return types here...
}
```

### ContextFactory

Once you've defined your `Context` and `ContextInterface`, you'll need to tell the package how to instantiate your 
it using a... `ContextFactory`! This package provides you with the `EonX\EasySecurity\Interfaces\ContextFactoryInterface`
you just have to do the implementation:

```php
// src/Security/Factories/ContextFactory.php

namespace App\Security\Factories;

use App\Security\Interfaces\ContextInterface;
use EonX\EasySecurity\Interfaces\ContextFactoryInterface;
use EonX\EasySecurity\Interfaces\Resolvers\ContextResolvingDataInterface;

final class ContextFactory implements ContextFactoryInterface
{
    public function create(ContextResolvingDataInterface $data): ContextInterface
    {
        return new Context($data->getApiToken(), $data->getRoles(), $data->getProvider(), $data->getUser());
    }
}
```

### Register ContextFactory as Service

For this package to be able to use your context factory you need to register it as a service under 
`EonX\EasySecurity\Interfaces\ContextFactoryInterface`:

```yaml
# config/services.yaml

services:
    # Other services...

    EonX\EasySecurity\Interfaces\ContextFactoryInterface: '@App\Security\Factories\ContextFactory'
```

## Context Data Providers

### User and Provider Interfaces

This package defines `EonX\EasySecurity\Interfaces\UserInterface` and `EonX\EasySecurity\Interfaces\ProviderInterface`.
Those interfaces are really basic so this package can work with a large panel of applications having different user and
provider objects. Same as `Context` and its interface you will use [Covariance][5].

The only thing you need to make sure is that the objects your application will provide to the context resolver are 
implementing those interfaces.

### Providers

This package handles all the generic tasks as decoding api token from request, fetch user id from api token, ...
All the application's specific logic is delegated to your application, logic isn't it!? To tell the package how to 
retrieve the object specific to your application you will use what we call "providers", this package provides the following interfaces:

- `EonX\EasySecurity\Interfaces\ProviderProviderInterface`: Provides the `Provider` instance
- `EonX\EasySecurity\Interfaces\UserProviderInterface`: Provides the `User` instance
- `EonX\EasySecurity\Interfaces\RolesProviderInterface`: Provides the list of `Role` instances

Your job is to create an implementation for each provider and register it as service.

```php
// src/Security/Providers/UserProvider.php

namespace App\Security\Providers;

use App\Repository\UserRepositoryInterface;
use EonX\EasySecurity\Interfaces\UserInterface;
use EonX\EasySecurity\Interfaces\UserProviderInterface;

final class UserProvider implements UserProviderInterface
{
    /**
     * @var \App\Repository\UserRepositoryInterface
     */
     private $userRepo;

    /**
     * UserProvider constructor.
     *
     * @param \App\Repository\UserRepositoryInterface $userRepo
     */
     public function __construct(UserRepositoryInterface $userRepo)
     {
        $this->userRepo = $userRepo;
     }

     /**
      * Get user for given uniqueId and data.
      *
      * @param int|string $uniqueId
      * @param mixed[] $data
      *
      * @return null|\EonX\EasySecurity\Interfaces\UserInterface
      */
      public function getUser($uniqueId, array $data): ?UserInterface
      {
          $user = $this->userRepo->findOneByExternalId($uniqueId);
     
          $user->setFirstName($data['fn'] ?? null);
          $user->setLastName($data['ln'] ?? null);
          $user->setExternalId($uniqueId);
     
          return $user;
      }
}
```

```php
// src/Security/Providers/ProviderProvider.php 

namespace App\Security\Providers;

use App\Repository\ProviderRepositoryInterface;
use EonX\EasySecurity\Interfaces\ProviderInterface;
use EonX\EasySecurity\Interfaces\ProviderProviderInterface;

final class ProviderProvider implements ProviderProviderInterface
{
    /**
     * @var \App\Repository\ProviderRepositoryInterface
     */
    private $providerRepo;

    /**
     * ProviderProvider constructor.
     *
     * @param \App\Repository\ProviderRepositoryInterface $providerRepo
     */
    public function __construct(ProviderRepositoryInterface $providerRepo)
    {
        $this->providerRepo = $providerRepo;
    }

    /**
     * Get provider for given uniqueId.
     *
     * @param int|string $uniqueId
     *
     * @return null|\EonX\EasySecurity\Interfaces\ProviderInterface
     */
    public function getProvider($uniqueId): ?ProviderInterface
    {
        return $this->providerRepo->findOne($uniqueId);
    }
}
```

```php
// src/Security/Providers/InMemoryRolesProvider.php

namespace App\Security\Providers;

use App\Security\Interfaces\PermissionsInterface;
use App\Security\Interfaces\RolesInterface;
use EonX\EasySecurity\Role;
use EonX\EasySecurity\RolesProviders\AbstractInMemoryRolesProvider;

final class InMemoryRolesProvider extends AbstractInMemoryRolesProvider
{
    /**
     * Init roles.
     *
     * @return \EonX\EasySecurity\Interfaces\RoleInterface[]
     */
    protected function initRoles(): array
    {
        return [
            new Role(RolesInterface::ROLE_SUPER_ADMIN, [
                PermissionsInterface::CATEGORY_CREATE,
                // Other permissions...
            ])
        ];
    }
}
```

```yaml
# config/services.yaml

services:
    # Other services...

    EonX\EasySecurity\Interfaces\ProviderProviderInterface: '@App\Security\Providers\ProviderProvider'
    EonX\EasySecurity\Interfaces\UserProviderInterface: '@App\Security\Providers\UserProvider'
    EonX\EasySecurity\Interfaces\RolesProviderInterface: '@App\Security\Providers\InMemoryRolesProvider'
```

### Data Resolvers

This package uses data resolvers to resolve the context data, it comes with built-in resolvers you can re-use or you
have the possibility to create your own by implementing `EonX\EasySecurity\Interfaces\Resolvers\ContextDataResolverInterface`.
Done! It will be automatically injected into the context resolver. If you need some resolvers to be executed in a specific
order you can use the `getPriority(): int` method to do so, the smallest priority will be executed first.

To use the built-in context data resolvers you just need to register them in your services config:

```yaml
# config/services.yaml

services:
    # Other services...

    EonX\EasySecurity\Resolvers\RolesFromJwtDataResolver: ~
    EonX\EasySecurity\Resolvers\ProviderFromJwtDataResolver: ~
    EonX\EasySecurity\Resolvers\ProviderFromHeaderDataResolver: ~
    EonX\EasySecurity\Resolvers\UserFromJwtDataResolver: ~
```  

### Configure Bundle

You need to tell the package to use your context interface as service id for the context, this way it will allow you
to use dependency injection with your own context interface!

```yaml
# config/packages/easy_security.yaml

easy_security:
    context_service_id: App\Security\Interfaces\ContextInterface
```

### Symfony Firewall and Authenticator

You're nearly there! The last step is to configure Symfony Security component to use all your previous work:

```yaml
# config/packages/security.yaml

security:
    firewalls:
        # Other firewalls...

        main:
            pattern: ^/.+
            stateless: true
            anonymous: false
            guard:
                authenticators:
                    - EonX\EasySecurity\Bridge\Symfony\Security\ContextAuthenticator
```

And you're done! Enjoy!

[1]: https://symfony.com
[2]: https://getcomposer.org/
[3]: https://github.com/eonx-com/easy-api-token
[4]: https://github.com/eonx-com/easy-psr7-factory
[5]: https://www.php.net/manual/en/language.oop5.variance.php
