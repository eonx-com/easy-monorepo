---eonx_docs---
title: Introduction
weight: 0
---eonx_docs---

# Introduction

The **EasyActivity** package allows you to log changes to objects in your applications, including the state of the
object before and after a change, and information about who initiated the change.

The following terms are used across the package:

- **Action**: An action performed on an object (i.e. "create", "update" or "delete").
- **Actor**: A person or process that performs an action. An actor is described by a type, an identifier, and a name.
- **Subject**: An object (entity, model, etc.) on which an action is performed.
- **Activity log entry**: A database entry that contains activity log data, i.e. information about a subject, the action
  performed on the subject, and the actor that performed the action.

Once configured with a set of subjects, the package will create activity log entries for every action performed on those
subjects. See [Configuration][1] for more information on configuring subjects.

The [ActivityLogEntry class][2] defines the data that can be recorded in an activity log entry.

See [Usage][3] for information on using the EasyActivity package.

[1]: config.md
[2]: activity-log-entry.md
[3]: usage.md
