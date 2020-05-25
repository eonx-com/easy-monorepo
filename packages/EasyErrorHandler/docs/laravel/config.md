---eonx_docs---
title: Configuration
weight: 2001
---eonx_docs---

### Create the configuration file

The package allows you to configure error response field names. Just copy `src/Bridge/Laravel/config/easy-error-handler.php` to `config/easy-error-handler.php` and adjust it for your needs (you can leave only the fields you want to override):

```php
# config/easy-error-handler.php

return [
    'response' => [
        'code' => 'code',
        'exception' => 'exception',
        'exception_class' => 'class',
        'exception_file' => 'file',
        'exception_line' => 'line',
        'exception_message' => 'message',
        'exception_trace' => 'trace',
        'message' => 'message',
        'sub_code' => 'sub_code',
        'time' => 'time',
        'violations' => 'violations',
    ],
];
```

### Translations

If you want to update default package translations, copy the `src/Bridge/Laravel/translations/en/messages.php` to the `resources/lang/vendor/easy-error-handler/en/messages.php` and change it.
