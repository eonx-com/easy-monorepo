---eonx_docs---
title: Usage
weight: 1002
---eonx_docs---

# Usage

The EasyActivity package stores activity log entries for actions performed on subjects. Activity log entries are stored
as database records in the `easy_activity_logs` table by default (the table name can be changed in the package
[configuration][1]). See the [ActivityLogEntry class][2] for more information on what can be stored in the database
record.

An application can either use `EonX\EasyActivity\Doctrine\Provider\DoctrineDbalStatementsProvider` to create a table or
describe a database entity/model relying on this table by itself.

## Resolving actors

To resolve an actor's identifier, name and type, the package relies on
`EonX\EasyActivity\Common\Resolver\ActorResolverInterface`.

Although a default implementation is provided by the package (`EonX\EasyActivity\Common\Resolver\DefaultActorResolver`), it
only sets the actor's type to the default (`system`), so your application should register its own implementation of the
interface to provide the required values (e.g. from a Security Context).

## Resolving subjects

To resolve a subject's identifier, type, data and old data, the package relies on
`EonX\EasyActivity\Common\Resolver\ActivitySubjectResolverInterface`.

The package provides a default implementation (`EonX\EasyActivity\Common\Resolver\DefaultActivitySubjectResolver`), but you
can implement your own instead.

## Creating activity log entries

To create a new activity log entry, an application can use one of the following methods:

- **EasyDoctrine**: Install the [eonx-com/easy-doctrine][3] package that provides events for Doctrine entity creation,
  update and deletion. EasyActivity has a bridge for EasyDoctrine that contains
  `EonX\EasyActivity\EasyDoctrine\Subscriber\EasyDoctrineEntityEventsSubscriber`, which will take care of accepting those
  events and passing them to `EonX\EasyActivity\Common\Factory\ActivityLogEntryFactoryInterface`. The bridge also passes the
  subject list from the EasyActivity configuration to the EasyDoctrine configuration (so the EasyDoctrine knows which
  Doctrine entities to listen to).
- **Eloquent**: Use an Eloquent bridge with a listener for Eloquent events that will take care of passing model data to
  `EonX\EasyActivity\Common\Factory\ActivityLogEntryFactoryInterface` (not implemented yet).
- **Manual creation**: Create activity log entries manually using
  `EonX\EasyActivity\Common\Factory\ActivityLogEntryFactoryInterface`, with either the default
  `EonX\EasyActivity\Common\Factory\ActivityLogEntryFactory` implementation or your own implementation registered for the interface.

To save a new activity log entry the package relies on `EonX\EasyActivity\Common\Logger\ActivityLoggerInterface`. An
application can register its own implementation or use one of the following:

- `EonX\EasyActivity\Common\Logger\AsyncActivityLogger` to save an activity log entry asynchronously (this is the default)
- `EonX\EasyActivity\Common\Logger\SyncActivityLogger` to save an activity log entry synchronously

## Symfony bridge

The Symfony bridge provided with this package allows it to be integrated into a Symfony-based application. Besides
Symfony bundle/extension classes, it brings the following functionality:

- The default implementation for `EonX\EasyActivity\Common\Serializer\ActivitySubjectDataSerializerInterface`:
  `EonX\EasyActivity\Common\Serializer\SymfonyActivitySubjectDataSerializer`, which is a simple wrapper for
  `Symfony\Component\Serializer\SerializerInterface` used to serialize activity log entry data. Please note that all
  the nested objects are serialized as an array containing only the `id` key by default. You can change the default
  behaviour with the `nested_object_allowed_properties` configuration option (see [Configuration][4]).
- The Symfony Messenger classes that are used for asynchronous activity log entry storing.
- The default implementation for `EonX\EasyActivity\Common\Store\StoreInterface`:
  `EonX\EasyActivity\Doctrine\Store\DoctrineDbalStore`, which stores the activity log entries using a DBAL connection.
  An application can register its own implementation for this interface to be able to store activity log entries in a
  different way or using different storage.

[1]: config.md

[2]: activity-log-entry.md

[3]: https://github.com/eonx-com/easy-doctrine

[4]: config.md
