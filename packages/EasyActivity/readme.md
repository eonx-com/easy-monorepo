---eonx_docs--- title: Introduction weight: 0 ---eonx_docs---

# Introduction

The **EasyActivity** package allows you to track your record changes.

The following terms are used across the package:

- **Log entry** — a DB entry that contains activity log data.
- **Action** — an action performed ("create", "update", "delete").
- **Actor** — a person or a process that performs an action. It is described by a type, an identifier, and a name.
- **Subject** — an object (entity, model, etc.) on which an action is performed.


## Require package (Composer)

The recommended way to install this package is to use [Composer][1]:

```bash
$ composer require eonx-com/easy-activity
```

[1]: https://getcomposer.org/
