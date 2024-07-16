---eonx_docs---
title: Configuration
weight: 1001
---eonx_docs---

# Configuration

You can configure settings for the EasyActivity package via a configuration file in your application.

By default, the EasyActivity package includes all properties of subjects in activity log entries. If desired, you can
configure activity log entries for each subject to only include specific properties or to always exclude specific
properties.

## Configuration files

For Symfony applications, the EasyActivity configuration file can be a YAML, XML or PHP file located under the
`config/packages` directory, with a name like `easy_activity.<format>`. The root node of the configuration must be
called `easy_activity`.

## Configuration options

The configuration options are as follows:

- `disallowed_properties`: An optional array of subject property names to be excluded from activity log entries globally
  (i.e. the list will be applied to all subjects defined in the `subjects` configuration option).
- `subjects`: A set of subject classes to be logged. Each subject can contain the following parameters:
    - `allowed_properties`: An optional array of subject property names to be allowed for activity log entries. If the
      option is present, only the specified properties will be included in activity log entries for the relevant subject.
    - `disallowed_properties`: An optional array of subject property names to be excluded from activity log entries for
      the relevant subject.
    - `nested_object_allowed_properties`: By default, nested objects within a subject only contain the `id` key. You can
      specify an optional set of classes that describe nested objects within the subject, each containing an array of
      property names to be included for activity log entries.
    - `type`: an optional subject type mapping. If no type is provided, a FQCN (Fully Qualified Class Name) will be used
      by default.
- `table_name`: Table name for storing activity log entries (the default is `easy_activity_logs`).

## Example configuration file

In Symfony, you could have a configuration file called `easy_activity.php` that looks like the following:

```php
<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Entity\SomeEntity;
use App\Entity\SomeOtherEntity;
use Symfony\Config\EasyActivityConfig;

return static function (EasyActivityConfig $easyActivityConfig): void {
    $easyActivityConfig
        ->tableName('activity_logs')
        ->disallowedProperties([
            'updatedAt',
        ]);
        
    $easyActivityConfig->subjects(SomeEntity::class)
        ->allowedProperties([
            'content',
            'description',
        ])
        ->disallowedProperties([
            'author',
        ])
        ->nestedObjectAllowedProperties([
            SomeOtherEntity::class => [
                'processingDate',
            ]
        ])
        ->type('SomeEntity');
};

```
