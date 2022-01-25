---eonx_docs---
title: Configuration
weight: 1001
---eonx_docs---

### Create the configuration file

If you're using [Symfony Flex][1], the `config/packages/easy_activity.yaml` config has been created automatically for you.
If not, you can add it yourself:

```yaml
easy_activity:
    table_name: activity_logs

    disallowed_properties:
        - updatedAt

    subjects:
#        App\Entity\SomeEntity:
#            allowed_properties:
#                - content
#                - description
#            disallowed_properties:
#                - author
#            nested_object_allowed_properties:
#                App\Entity\SomeAnotherEntity:
#                    - processingDate
#
```

#### Settings for entities
* `disallowed_properties` — an optional global array of "Subject" property names to be excluded from log entries (this list will be applied to all "Subjects").
* `subjects` — a list of "Subject" classes to be logged. Each item can contain the following params:
  * `allowed_properties` — an optional array of "Subject" property names to be "whitelisted" for log entries.
  * `disallowed_properties` — an optional array of "Subject" property names to be excluded from log entries.
  * `nested_object_allowed_properties` — an optional property names to be "whitelisted" for log related entries.
  * `type` — an optional "Subject" type mapping. If no type is provided, a FQCN will be used by default.


[1]: https://flex.symfony.com/
