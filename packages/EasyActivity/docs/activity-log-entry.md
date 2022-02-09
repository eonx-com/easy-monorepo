---eonx_docs---
title: ActivityLogEntry class
weight: 1003
---eonx_docs---

# ActivityLogEntry class

The `EonX\EasyActivity\ActivityLogEntry` class defines the data that can be recorded in an activity log entry.

## Properties

An ActivityLogEntry object has the following properties:

- `action`: The type of action performed on the subject, which may be one of:
  - `ActivityLogEntry::ACTION_CREATE` (i.e. `create`): Create subject
  - `ActivityLogEntry::ACTION_DELETE` (i.e. `delete`): Delete subject
  - `ActivityLogEntry::ACTION_UPDATE` (i.e. `update`): Update subject
- `actorId`: An optional identifier for an actor in the application.
- `actorName`: An optional name for an actor in the application.
- `actorType`: A mandatory actor type. The actor type could be a `user`, `provider`, `customer`, `jwt:provider`,
  `api_key:customer`, or something similar in an application. The default value is
  `ActivityLogEntry::DEFAULT_ACTOR_TYPE` (i.e. `system`).
- `subjectId`: An optional identifier for a subject in the application.
- `subjectType`: A mandatory subject type in the application. The subject type can be a short class name, a FQCN (Fully
  Qualified Class Name), or any arbitrary string that an application maps in the package [configuration][1].
- `data`: An optional representation of the state of the subject after applying the action (i.e. a serialized
  entity/model containing the new attribute values of the subject after updating the entity/model). This is a simple
  key-value array with attribute names in keys.
- `oldData`: An optional representation of the state of the subject before applying the action (i.e. a serialized
  entity/model containing the original attribute values before updating the entity/model). This is a simple key-value
  array with attribute names in keys.
- `id`: A UUID generated in the default store implementation.
- `createdAt`: Always set to "now" by the default store implementation.
- `updatedAt`: Always set to "now" by the default store implementation.

## ActivityLogEntry creation

The package provides `EonX\EasyActivity\Interfaces\ActivityLogEntryFactoryInterface` that takes care of activity log
entry creation. A default implementation `EonX\EasyActivity\ActivityLogEntryFactory` is also provided by the package.
See [Usage][2] for more information on using the package.

[1]: config.md
[2]: usage.md
