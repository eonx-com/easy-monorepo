---eonx_docs---
title: Doctrine ORM SQL Logger
weight: 2002
---eonx_docs---

Having the list of SQL queries executed during a request which triggered an error/exception to be notified is really
important, it makes debugging easier. That's why `easy-bugsnag` got you sorted!

If your app is using Laravel/Lumen and [Laravel Doctrine ORM][1], this section is for you.

### Enable Logging In EasyBugsnag Config

To enable SQL queries logging into your Bugsnag reports, simply set the `doctrine_orm` config to true:

```php
// config/easy-bugsnag.php

return [
    'api_key' => \env('BUGSNAG_API_KEY'),
    
    'doctrine_orm' => true,
]; 
```

### Register SQL Logger Into Doctrine Config

Then you will need to register the SQL Logger from EasyBugsnag into Doctrine configuration:

```php
// config/doctrine.php

return [
    // ...

    'logger' => EonX\EasyBugsnag\Bridge\Laravel\Doctrine\SqlOrmLogger::class,

    // ...
]; 
```

[1]: http://www.laraveldoctrine.org/docs/1.4/orm
