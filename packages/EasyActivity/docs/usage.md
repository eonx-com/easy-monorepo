<!---eonx_docs---
title: Usage
weight: 1002
---eonx_docs--->

# Usage

The EasyActivity package stores activity log entries for actions performed on subjects.

## Resolving actors

A default implementation is provided by the package ([`EonX\EasyActivity\Common\Resolver\DefaultActorResolver`](../src/Common/Resolver/DefaultActorResolver.php))
, it only sets the actor's type to the default (`system`), so your application should register
its own implementation of the interface to provide the required values (e.g. from a Security Context).

For example, if you are using the EasySecurity package, you can create the following implementation `src/Infrastructure/EasyActivity/Resolver/ActorResolver.php`

```php
<?php
declare(strict_types=1);

namespace App\Infrastructure\EasyActivity\Resolver;

use EonX\EasyActivity\Actor;
use EonX\EasyActivity\Interfaces\ActorResolverInterface;
use EonX\EasySecurity\Interfaces\ProviderInterface;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use EonX\EasySecurity\Interfaces\SecurityContextResolverInterface;
use EonX\EasySecurity\Interfaces\UserInterface;
use UnexpectedValueException;

final class ActorResolver implements ActorResolverInterface
{
    public const ACTOR_TYPE_API_KEY_PROVIDER = 'api_key:provider';

    private const ACTOR_TYPE_JWT_PROVIDER = 'jwt:provider';

    private const ACTOR_TYPE_USER = 'user';

    public function __construct(private SecurityContextResolverInterface $securityContextResolver)
    {
    }

    public function resolve(object $object): Actor
    {
        $securityContext = $this->securityContextResolver->resolveContext();
        $user = $securityContext->getUser();
        $provider = $securityContext->getProvider();

        return match (true) {
            $user !== null => $this->resolveUserActor($user, $securityContext),
            $provider !== null => $this->resolveProviderActor($provider, $securityContext),
            default => throw new UnexpectedValueException("Actor couldn't be resolved."),
        };
    }

    private function resolveProviderActor(ProviderInterface $provider, SecurityContextInterface $securityContext): Actor
    {
        $token = $securityContext->getToken();

        if ($token !== null) {
            return new Actor(
                self::ACTOR_TYPE_API_KEY_PROVIDER,
                $token->getPayload()['sub'],
                $provider->getName()
            );
        }

        return new Actor(self::ACTOR_TYPE_JWT_PROVIDER, $provider->getExternalId(), $provider->getFullName());
    }

    private function resolveUserActor(UserInterface $user, SecurityContextInterface $securityContext): Actor
    {
        return new Actor(self::ACTOR_TYPE_USER, $user->getUserIdentifier(), $user->getFullName());
    }
}

```

And add service definition to your Symfony configuration:

```php
<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Infrastructure\EasyActivity\Resolver\ActorResolver;
use EonX\EasyActivity\Interfaces\ActorResolverInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(ActorResolverInterface::class, ActorResolver::class);
};

```

## Resolving subjects

The package provides a default implementation ([`EonX\EasyActivity\Common\Resolver\DefaultActivitySubjectResolver`](../src/Common/Resolver/DefaultActivitySubjectResolver.php))
, but you can implement your own instead.

By default the package use FQCN for the subject type. You could set the subject type in the configuration
for each subject or implement your own resolver `src/Infrastructure/EasyActivity/Resolver/ActivitySubjectResolver.php`:

```php
<?php
declare(strict_types=1);

namespace App\Infrastructure\EasyActivity\Resolver;

use EonX\EasyActivity\ActivitySubject;
use EonX\EasyActivity\Interfaces\ActivitySubjectInterface;
use EonX\EasyActivity\Interfaces\ActivitySubjectResolverInterface;
use ReflectionClass;

final class ActivitySubjectResolver implements ActivitySubjectResolverInterface
{
    public function __construct(private ActivitySubjectResolverInterface $decorated)
    {
    }

    public function resolve(object $object): ?ActivitySubjectInterface
    {
        $activitySubject = $this->decorated->resolve($object);

        if ($activitySubject !== null) {
            $activitySubject = new ActivitySubject(
                $activitySubject->getActivitySubjectId(),
                $this->resolveSubjectType($activitySubject->getActivitySubjectType()),
                $activitySubject->getDisallowedActivityProperties(),
                $activitySubject->getNestedObjectAllowedActivityProperties(),
                $activitySubject->getAllowedActivityProperties()
            );
        }

        return $activitySubject;
    }

    private function resolveSubjectType(string $subjectType): string
    {
        if (\class_exists($subjectType)) {
            $subjectType = (new ReflectionClass($subjectType))->getShortName();
        }

        return $subjectType;
    }
}

```

And add service definition to your Symfony configuration:

```php
<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Infrastructure\EasyActivity\Resolver\ActivitySubjectResolver;
use EonX\EasyActivity\Interfaces\ActivitySubjectResolverInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(ActivitySubjectResolver::class)
        ->arg('$decorated', service('.inner'))
        ->decorate(ActivitySubjectResolverInterface::class);
};

```

## Creating activity log entries

The [eonx-com/easy-doctrine](https://github.com/eonx-com/easy-doctrine) package provides events for Doctrine entity creation,
update and deletion. EasyActivity has integration with EasyDoctrine that contains
[`EonX\EasyActivity\EasyDoctrine\Subscriber\EasyDoctrineEntityEventsSubscriber`](../src/EasyDoctrine/Subscriber/EasyDoctrineEntityEventsSubscriber.php)
, which will take care of accepting those events and passing them to
[`EonX\EasyActivity\Common\Factory\ActivityLogEntryFactory`](../src/Common/Factory/ActivityLogEntryFactory.php)
EasyActivity also passes the subject list to the EasyDoctrine configuration
(so the EasyDoctrine knows which Doctrine entities to listen to).

Also you could create activity log entries manually.

## Serializing activity log entry data

Package provides the
[`EonX\EasyActivity\Common\Serializer\SymfonyActivitySubjectDataSerializer`](../src/Common/Serializer/SymfonyActivitySubjectDataSerializer.php)
, which is a simple wrapper for
`Symfony\Component\Serializer\SerializerInterface` used to serialize activity log entry data.
Please note that all the nested objects are serialized as an array containing only the `id` key by default.
You can change the default behaviour with the `nested_object_allowed_properties` configuration option
(see [Configuration](config.md)).

## Storing activity log entries

Activity log entries are stored as database records in the `easy_activity_logs` table by default
(the table name can be changed in the package [configuration](config.md)).

An application can either use
[`EonX\EasyActivity\Doctrine\Provider\DoctrineDbalStatementProvider`](../src/Doctrine/Provider/DoctrineDbalStatementProvider.php)
to create a table or describe a database entity/model relying on this table by itself.

By default package uses the
[`EonX\EasyActivity\Doctrine\Store\DoctrineDbalStore`](../src/Doctrine/Store/DoctrineDbalStore.php)
, which stores the activity log entries using a DBAL connection.
