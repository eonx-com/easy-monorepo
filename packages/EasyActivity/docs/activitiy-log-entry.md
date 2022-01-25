---eonx_docs---
title: 'ActivityLogEntry class'
weight: 1003
---eonx_docs---

# ActivityLogEntry class

The **ActivityLogEntry** class defines an activity log data.

## Properties

A `ActivityLogEntry` object has the following properties:

- `action`: an action type, one of: `ActivityLogEntry::ACTION_CREATE`, `ActivityLogEntry::ACTION_DELETE`, or `ActivityLogEntry::ACTION_UPDATE`.
- `actorId`: an optional "Actor" identifier in an application.
- `actorName`: an optional "Actor" name in an application.
- `actorType`: a mandatory "Actor" type, can be a "user", "provider", "customer", "jwt:provider", "api_key:customer", or something similar in an application. The default value is `ActivityLogEntry::DEFAULT_ACTOR_TYPE` (`system`).
- `subjectId`: an optional "Subject" identifier in an application.
- `subjectType`: a mandatory "Subject" type in an application. This can be a short class name, an FQCN, or any arbitrary string an application maps in the config.
- `data`: an optional representation of the logged "Subject" (i.e. a serialized entity/model data using `SerializerInterface`). This is a simple key-value array with attribute names in keys.
- `oldData`: an optional representation of the state of the logged "Subject" before applying an action (i.e. a simple key-value array with the original attribute values before updating the entity/model).
- `id`: a UUID generated in the default store implementation.
- `createdAt`: always set to "now" by the default store implementation.
- `updatedAt`: always set to "now" by the default store implementation.

### ActivityLogEntry creation

The package provides `ActivityLogEntryFactoryInterface` (along with its `ActivityLogEntryFactory` implementation) 
that takes care of a log entry creation.
