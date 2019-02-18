<div align="center">
    <h1>StepTheFkUp - MockBuilder</h1>
    <p>Provides an easier way to mock repetitive expectations.</p>
</div>

---

Have you ever find yourself doing these over and over with a class you want to mock?

```php
$expectedUser = new User();
$mockManager = Mockery::mock(ObjectManager::class);
$mockManager->shouldReceive('find')
    ->once()
    ->with(User::class, 'the-user-id')
    ->andReturn(new User());
$mockManager->shouldReceive('find')
    ->once()
    ->with(Post::class, 'the-post-id')
    ->andReturn(new Post());
$mockManager->shouldReceive('find')
    ->once()
    ->with(User::class, 'the-user-id-2', 'extra param?')
    ->andReturn(new User());
// and so on...
```
    
Do you feel annoyed and tired of typing these long mock expectations and possibly make mistakes while doing it. Look at the 3rd expectation - the 3rd parameter in `with` does not exist in `find` method of Doctrine's `ObjectManager`. You will have a hard time debugging it when you have lots of mocking in your tests!

**What if you can refactor your tests to look like this?**
```php
$mockManager = (new ObjectManagerMockBuilder())
    ->hasFind(User::class, 'the-user-id')
    ->hasFind(Post::class, 'the-post-id')
    ->hasFind(User::class, 'the-user-id-2', 'extra-param')
    ->build();
```
Better? Not only that your code becomes more readable, your IDE will show you the available methods you can call like `hasFind`, `hasPersist`, `hasRemove`, and more depending on the `MockBuilder` implementation. 

Also, would you believe that your IDE will warn you with the arguments you are passing? For example in `hasFind(User::class, 'the-user-id-2', 'extra-param')`, this will give you a warning that you passed 3 arguments instead of just 2. If you do this: `hasFind(1234, 'the-user-id')`, this will give you a warning that the first parameter is expected to be a string! 

Awesome right? LOL! Enough with this advertisement and let's proceed to installation :D

# Documentation

## Installation

The recommended way to install this package is to use [Composer][1].

```bash
$ composer require --dev stepthefkup/mock-builder
```

## MockBuilder Implementations
- Doctrine ORM
  1. **EntityManagerMockBuilder** - `\Doctrine\ORM\EntityManagerInterface`
  2. **EntityRepositoryMockBuilder** - `\Doctrine\ORM\EntityRepository`
  3. **ManagerRegistryMockBuilder** - `\Doctrine\Common\Persistence\ManagerRegistry`
  4. **ObjectManagerMockBuilder** - `\Doctrine\Common\Persistence\ObjectManager`
  5. **ObjectRepositoryMockBuilder** - `\Doctrine\Common\Persistence\ObjectRepository`
  6. **QueryBuilderMockBuilder** -`\Doctrine\ORM\QueryBuilder`

## Creating new MockBuilders

1. Extend `StepTheFkUp\MockBuilder\AbstractMockBuilder`
    ```php
    class EntityRepositoryMockBuilder extends AbstractMockBuilder
    ```
2. Implement method `getClassToMock` to return the class name you want to mock.
    ```php
    protected function getClassToMock(): string
    {
        return ObjectRepository::class;
    }
    ```
3. Add `@method` in the class PHPdoc block with the expected methods signature prepended with `has`.
    ```php
    /**
     * @method self hasClear()
     * @method self hasCount(array $criteria)
     * @method self hasCreateNamedQuery($queryName)
     * @method self hasCreateNativeNamedQuery($queryName)
     * @method self hasCreateQueryBuilder(string $alias, ?string $indexBy = null)
     * @method self hasCreateResultSetMappingBuilder(string $alias)
     * @method self hasGetMetadataFactory()
     * @method self hasMatching(\Doctrine\Common\Collections\Criteria $criteria)
     *
     * @see \Doctrine\ORM\EntityRepository
     */
    ```
4. Use them in your tests.
    ```php
    /** @var \Doctrine\ORM\QueryBuilder $queryBuilder */
    $queryBuilder = (new QueryBuilderMockBuilder())->build();

    /** @var \Doctrine\ORM\EntityRepository $repository */
    $repository = (new EntityRepositoryMockBuilder())
        ->hasCreateQueryBuilder('alias', 'index-by')
        ->andReturn($queryBuilder)
        ->build();

    $this->assertEquals(
        $queryBuilder,
        $repository->createQueryBuilder('alias', 'index-by')
    );
    ```