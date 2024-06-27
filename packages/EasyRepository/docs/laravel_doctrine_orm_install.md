---eonx_docs---
title: Doctrine ORM
weight: 2001
---eonx_docs---

This document describes the steps to use this package into a [Laravel][1] and/or [Lumen][2] application with the
[Doctrine ORM][3] implementation.

<br>

# Install With Doctrine ORM

For this package install please refer to the related [documentation](laravel_install.md). Regarding Doctrine ORM, we
strongly recommend you to use the [Laravel Doctrine ORM package][4], please refer to its documentation for its install.

<br>

# Usage

## AbstractDoctrineRepository

This package provides you with `EonX\EasyRepository\Implementations\Doctrine\ORM\AbstractDoctrineRepository` which
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
use EonX\EasyRepository\Implementations\Doctrine\ORM\AbstractDoctrineRepository;

final class PostRepository extends AbstractDoctrineRepository implements PostRepositoryInterface
{
    /**
     * Find a post by its title.
     *
     * @param string $title The post title
     *
     * @return null|\App\Database\Entities\Post
     */
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

<br>

## AbstractPaginatedDoctrineOrmRepository

If you need to deal with paginated sets of objects, this package provides you with `EonX\EasyRepository\Repository\AbstractPaginatedDoctrineOrmRepository`
which has everything setup for you. This repository has a dependency on the `StartSizeDataInterface` from the `eonx/pagination`
package so to be able to use it you first need to update your project's dependencies:

```bash
$ composer require eonx/pagination
```

And then don't forget to register the service provider into your application, please refer to the [documentation][5].

Once everything setup, the pagination data will be resolved and injected automatically into your repositories. You will
have access to the method `public function paginate(?StartSizeDataInterface $startSizeData = null): LengthAwarePaginatorInterface`
which will allow you to pass an optional pagination data object, if you don't it will then use the resolved data.
On the top of this public method, this abstraction provides an internal `protected function doPaginate(\Doctrine\ORM\Query $query): LengthAwarePaginatorInterface`
which allows you to easily paginate any custom query you build.

Here is a simple example how to use it:

```php
// app/Repositories/PostRepositoryInterface.php

use EonX\EasyPagination\Paginator\LengthAwarePaginatorInterface;

interface PostRepositoryInterface
{
    public function getByCategoryPaginatedList(string $category): LengthAwarePaginatorInterface;
}

// app/Repositories/PostRepository.php

use App\Database\Entities\Post;
use EonX\EasyRepository\Repository\AbstractPaginatedDoctrineOrmRepository;

final class PostRepository extends AbstractPaginatedDoctrineOrmRepository implements PostRepositoryInterface
{
    public function getByCategoryPaginatedList(string $category): LengthAwarePaginatorInterface
    {
        $query = $this->createQueryBuilder()
                      ->where('p.category = :category')
                      ->setParameter('category', $category)
                      ->getQuery();

        return $this->doPaginate($query);
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

[5]: https://github.com/eonx/easy-pagination/blob/master/docs/install_laravel.md
