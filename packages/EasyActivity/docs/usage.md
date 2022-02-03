---eonx_docs---
title: Usage
weight: 1002
---eonx_docs---

# Usage

A [log DB entry](1) is stored in the `easy_activity_logs` table by default (the table name can be changed in the config). 
An application can either use `\EonX\EasyActivity\Bridge\Doctrine\DoctrineDbalStatementsProvider` to create a table or describe a DB entity/model relying on this table by itself.

* To resolve the "Actor" identifier, name, and type, the package relies on `\EonX\EasyActivity\Interfaces\ActorResolverInterface`.
An application **should** register its own implementation for this interface to provide these values (e.g. from a Security Context).
* To resolve the "Subject" identifier, type, data and old data, the package relies on `\EonX\EasyActivity\Interfaces\ActivitySubjectResolverInterface`. The package provides default implementation `\EonX\EasyActivity\Resolvers\DefaultActivitySubjectResolver`.
* To create a new log entry, an application can rely on one of the following ways:
  * Install the [eonx-com/easy-doctrine](2) package that provides events for Doctrine entity creation/update/deletion.
  * The `EasyDoctrine` bridge in the `EasyActivity` package has `\EonX\EasyActivity\Bridge\EasyDoctrine\EasyDoctrineEntityEventsSubscriber` that will take care of accepting those events and passing them to `EonX\EasyActivity\Interfaces\ActivityLogEntryFactoryInterface`. 
The bridge also passes the subject list from the `EasyActivity` config to the `EasyDoctrine` config (so `EasyDoctrine` knows what Doctrine entities to listen to).
  * Use an Eloquent bridge with a listener for Eloquent events that will take care of passing model data to `\EonX\EasyActivity\Interfaces\ActivityLogEntryFactoryInterface` (not implemented yet).
  * Create an entry manually using `\EonX\EasyActivity\Interfaces\ActivityLogEntryFactoryInterface` (with either the default `\EonX\EasyActivity\ActivityLogEntryFactory` implementation or its own implementation registered for this interface).
* To save a new log entry the package relies on `\EonX\EasyActivity\Interfaces\ActivityLoggerInterface`. An application can register its own implementation or use one of:
  * `\EonX\EasyActivity\Logger\AsyncActivityLogger` to save an entry asynchronyously. It's used be default.
  * `\EonX\EasyActivity\Logger\SyncActivityLogger` to save an entry synchronyously.
* The Symfony bridge provided with this package allows to integrate it into a Symfony-based application. Besides Symfony bundle/extension classes, it brings the following functionality:
  * The default implementation for `\EonX\EasyActivity\Interfaces\ActivitySubjectDataSerializerInterface` - `\EonX\EasyActivity\Bridge\Symfony\Serializers\SymfonyActivitySubjectDataSerializer`, a simple wrapper for `\Symfony\Component\Serializer\SerializerInterface` 
to serialize the given data. Please note that all the nested objects are serialized as an array containing the only `id` key by default (you can change it with the `nested_object_allowed_properties` setting).
  * The Symfony Messenger classes that are used for asynchronous log entry storing.
  * The default implementation for StoreInterface - `\EonX\EasyActivity\Bridge\Doctrine\DoctrineDbalStore` that stores the given log entry using a DBAL connection. 
An application can register its own implementation for this interface to be able to store log entries in a different way or using different storage.

[1]: activitiy-log-entry.md
[2]: https://github.com/eonx-com/easy-doctrine
