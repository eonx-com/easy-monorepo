---eonx_docs---
title: Translations
weight: 1009
---eonx_docs---

# Translations

## Symfony

You can set the Symfony translation domain for the package via the `translation_domain` configuration option (see
[Configuration](config.md)). You can also set the translation domain for individual EasyErrorHandler exceptions via the
exception's `setDomain()` method.

If you want to update the default package translations, copy the file
`bundle/translations/EasyErrorHandlerBundle.en.php` from the package to
`translations/EasyErrorHandlerBundle.en.php` in your project and edit the file as required.

## Laravel

If you want to update the default package translations, copy the file `laravel/translations/en/exceptions.php`
from the package to `resources/lang/vendor/easy-error-handler/en/exceptions.php` in your project and edit the file as
required.
