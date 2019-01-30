<div align="center">
    <h1>StepTheFkUp - EasyRepository</h1>
    <p>Provides an easy way to implement the Repository Design Pattern in your applications.</p>
</div>

---

This document describes the steps to use this package into a [Laravel][1] and/or [Lumen][2] application with the
[Doctrine ORM][3] implementation.

## Install With Doctrine ORM

For this package install please refer to the related [documentation](laravel_install.md). Regarding Doctrine ORM, we
strongly recommend you to use the [Laravel Doctrine ORM package][4], please refer to its documentation for its install.

## Usage

This package provides you with `StepTheFkUp\EasyRepository\Implementations\Doctrine\AbstractDoctrineRepository` which
has everything setup for you to implement the generic `ObjectRepositoryInterface` using Doctrine ORM, and take advantage
of the dependency injection. Using this abstraction is completely optional you could create your own, but we recommend
to use it to avoid you doing work already done.

Make sure all your repositories extend it, then you will be able to use the existing methods and/or create your own. To
be able to work properly this abstraction will ensure an entity class is provided.


Here is a simple example how to use it:

```php
// app/Repositories/PostRepositoryInterface.php

use App\Database\Entities\Post;

interface PostRepositoryInterface
{
    public function findByTitle(string $title): ?Post;
}

// app/Repositories/PostRepository.php

use App\Database\Entities\Post;
use StepTheFkUp\EasyRepository\Implementations\Doctrine\AbstractDoctrineRepository;

final class PostRepository extends AbstractDoctrineRepository implements PostRepositoryInterface
{
    public function findByTitle(string $title): ?Post
    {
        // The abstraction provides you the Doctrine Repository for Post::class as a protected property
        return $this->repository->findOneBy(['title' => $title]);
    }

    /**
     * Get entity class managed by the repository.
     *
     * @return string
     */
     protected function getEntityClass(): string
     {
        return Post::class;
     }
}
```

[1]: https://laravel.com/
[2]: https://lumen.laravel.com/
[3]: https://www.doctrine-project.org/projects/orm.html
[4]: https://www.laraveldoctrine.org/docs/1.3/orm
