---eonx_docs---
title: Laravel
weight: 2000
is_section: true
section_icon: fab fa-laravel
---eonx_docs---

This document describes the steps to install this package into a [Laravel][1] application.

<br>

# Service Provider

Once the package required, you must tell your application to use it. Laravel uses service providers to do so, if you are
not familiar with this concept make sure to have a look at the [documentation][4].

This package provides you with a service provider which will register your repositories into the services container
automatically. Make sure to register it:

```php
// config/app.php

'providers' => [
    // Other Service Providers...

    \EonX\EasyRepository\Laravel\EasyRepositoryServiceProvider::class,
],
```

<br>

# Config

To allow this package to work with your own repositories you must let it know about your repositories structure. To do
so you will use the configuration file `laravel/config/easy-repository.php`. Copy/Paste this file into your
`config` folder and then update it with your own repositories list.

```php
return [
    'repositories' => [
        \App\Repositories\PostRepositoryInterface::class => \App\Repositories\PostRepository::class,
        \App\Repositories\CommentRepositoryInterface::class => \App\Repositories\CommentRepository::class,
    ],
```

Repositories list must be an associative array where the keys are the abstraction of your repositories
and the values the concrete class of your repositories. The keys of this array can technically be anything, however,
we strongly recommend you to use the [FQCN][5] of the interface your repository implements this way you can use
[autowiring][6] for your dependency injection.

<br>

# Usage

That's it you're all setup! You're now able to use your repositories as services anywhere you want, using dependency
injection or service locator (we strongly recommend to use the first one haha).

```php
// Dependency Injection
public function __construct(\App\Repositories\PostRepositoryInterface $postRepository) {
    $this->postRepository = $postRepository; // Will be your configured repository implementation
}

// Service Locator
$app->make(\App\Repositories\PostRepositoryInterface::class); // Will be your configured repository implementation as well
```

[1]: https://laravel.com/

[4]: https://laravel.com/docs/10.x/providers

[5]: https://en.wikipedia.org/wiki/Fully_qualified_name

[6]: http://php-di.org/doc/autowiring.html
