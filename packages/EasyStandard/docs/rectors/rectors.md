---eonx_docs---
title: 'EasyStandard: Rectors'
weight: 2001
---eonx_docs---
##### [\EonX\EasyStandard\Rector\AddCoversAnnotationRector][1]
Adds `@covers` annotation for test classes.
```php
// before
class SomeServiceTest extends \PHPUnit\Framework\TestCase
{
}
```
```php
// after
/**
 * @covers \SomeService
 */
class SomeServiceTest extends \PHPUnit\Framework\TestCase
{
}
```
**Parameters**

- `replaceArray` - An array of strings that will be cut from the FQCN (Fully Qualified Class Name) when searching for the class covered by this test. Default value: `[]`.

##### [\EonX\EasyStandard\Rector\AddSeeAnnotationRector][2]
Adds `@see` annotation for data providers.
```php
// before
/**
 * Provides some data.
 *
 * @return mixed[]
 */
public function provideSomeData(): array
{
}
```
```php
// after
/**
 * Provides some data.
 *
 * @return mixed[]
 *
 * @see testMethod
 */
public function provideSomeData(): array
{
}
```

##### [\EonX\EasyStandard\Rector\AnnotationsCommentsRector][3]
Comments should have punctuation marks at the end of the sentence.
```php
// before
/**
 * Some class
 */
class SomeClass
{
}
```
```php
// after
/**
 * Some class.
 */
class SomeClass
{
}
```
##### [\EonX\EasyStandard\Rector\ExplicitBoolCompareRector][4]
Makes bool conditions prettier.
```php
// before
final class SomeController
{
    public function run($items)
    {
        if (\is_array([]) === true) {
            return 'is array';
        }
    }
}
```
```php
// after
final class SomeController
{
    public function run($items)
    {
        if (\is_array([])) {
            return 'is array';
        }
    }
}
```

##### [\EonX\EasyStandard\Rector\InheritDocRector][5]
Replaces `{@inheritdoc}` annotation with `{@inheritDoc}`.
```php
// before
/**
 * {@inheritdoc}
 */
public function someMethod(): array
{
}
```
```php
//after
/**
 * {@inheritDoc}
 */
public function someMethod(): array
{
}
```

##### [\EonX\EasyStandard\Rector\RestoreDefaultNullToNullableTypeParameterRector][6]
Adds default null value to function arguments with PHP 7.1 nullable type.
```php
// before
class SomeClass
{
    public function __construct(?string $value)
    {
         $this->value = $value;
    }
}
```
```php
// after
class SomeClass
{
    public function __construct(?string $value = null)
    {
         $this->value = $value;
    }
}
```

##### [\EonX\EasyStandard\Rector\StrictInArrayRector][7]
Makes in_array calls strict.
```php
// before
\in_array($value, $items);
```
```php
// after
\in_array($value, $items, true);
```

##### [\EonX\EasyStandard\Rector\UselessSingleAnnotationRector][8]
Removes PHPDoc completely if it contains only useless single annotation.
```php
// before
/**
 * {@inheritDoc}
 */
public function someMethod(): array
{
}
```
```php
// after
public function someMethod(): array
{
}
```

[1]: https://github.com/eonx-com/easy-monorepo/blob/master/packages/EasyStandard/src/Rector/AddCoversAnnotationRector.php
[2]: https://github.com/eonx-com/easy-monorepo/blob/master/packages/EasyStandard/src/Rector/AddSeeAnnotationRector.php
[3]: https://github.com/eonx-com/easy-monorepo/blob/master/packages/EasyStandard/src/Rector/AnnotationsCommentsRector.php
[4]: https://github.com/eonx-com/easy-monorepo/blob/master/packages/EasyStandard/src/Rector/ExplicitBoolCompareRector.php
[5]: https://github.com/eonx-com/easy-monorepo/blob/master/packages/EasyStandard/src/Rector/InheritDocRector.php
[6]: https://github.com/eonx-com/easy-monorepo/blob/master/packages/EasyStandard/src/Rector/RestoreDefaultNullToNullableTypeParameterRector.php
[7]: https://github.com/eonx-com/easy-monorepo/blob/master/packages/EasyStandard/src/Rector/StrictInArrayRector.php
[8]: https://github.com/eonx-com/easy-monorepo/blob/master/packages/EasyStandard/src/Rector/UselessSingleAnnotationRector.php
