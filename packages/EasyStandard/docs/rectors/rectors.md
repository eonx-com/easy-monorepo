---eonx_docs--- title: 'EasyStandard: Rectors' weight: 2001 ---eonx_docs---

##### [\EonX\EasyStandard\Rector\AddCoversAnnotationRector][1]

Adds `@covers` annotation for test classes.

```php
// Before
class SomeServiceTest extends \PHPUnit\Framework\TestCase
{
}
```

```php
// After
/**
 * @covers \SomeService
 */
class SomeServiceTest extends \PHPUnit\Framework\TestCase
{
}
```

**Parameters**

- `replaceArray` - An array of strings that will be cut from the FQCN (Fully Qualified Class Name) when searching for
  the class covered by this test. Default value: `[]`.

##### [\EonX\EasyStandard\Rector\AddSeeAnnotationRector][2]

Adds `@see` annotation for data providers.

```php
// Before
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
// After
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

##### [\EonX\EasyStandard\Rector\ExplicitBoolCompareRector][3]

Makes bool conditions prettier.

```php
// Before
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
// After
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

##### [\EonX\EasyStandard\Rector\InheritDocRector][4]

Replaces `{@inheritdoc}` annotation with `{@inheritDoc}`.

```php
// Before
/**
 * {@inheritdoc}
 */
public function someMethod(): array
{
}
```

```php
// After
/**
 * {@inheritDoc}
 */
public function someMethod(): array
{
}
```

##### [\EonX\EasyStandard\Rector\PhpDocCommentRector][5]

Applies the company standards to PHPDoc descriptions.

```php
// Before
/**
 * some class
 */
class SomeClass()
{
}
```

```php
// After
/**
 * Some class.
 */
class SomeClass()
{
}
```

##### [\EonX\EasyStandard\Rector\RestoreDefaultNullToNullableTypeParameterRector][6]

Adds default null value to function arguments with PHP 7.1 nullable type.

```php
// Before
class SomeClass
{
    public function __construct(?string $value)
    {
         $this->value = $value;
    }
}
```

```php
// After
class SomeClass
{
    public function __construct(?string $value = null)
    {
         $this->value = $value;
    }
}
```

##### [\EonX\EasyStandard\Rector\SingleLineCommentRector][7]

Applies the company standards to single-line comments.

```php
// Before

// some class.
class SomeClass
{
}
```

```php
// After

// Some class
class SomeClass
{
}
```

##### [\EonX\EasyStandard\Rector\StrictInArrayRector][8]

Makes in_array calls strict.

```php
// Before
\in_array($value, $items);
```

```php
// After
\in_array($value, $items, true);
```

##### [\EonX\EasyStandard\Rector\UselessSingleAnnotationRector][9]

Removes PHPDoc completely if it contains only useless single annotation.

```php
// Before
/**
 * {@inheritDoc}
 */
public function someMethod(): array
{
}
```

```php
// After
public function someMethod(): array
{
}
```

[1]: https://github.com/eonx-com/easy-monorepo/blob/master/packages/EasyStandard/src/Rector/AddCoversAnnotationRector.php

[2]: https://github.com/eonx-com/easy-monorepo/blob/master/packages/EasyStandard/src/Rector/AddSeeAnnotationRector.php

[3]: https://github.com/eonx-com/easy-monorepo/blob/master/packages/EasyStandard/src/Rector/ExplicitBoolCompareRector.php

[4]: https://github.com/eonx-com/easy-monorepo/blob/master/packages/EasyStandard/src/Rector/InheritDocRector.php

[5]: https://github.com/eonx-com/easy-monorepo/blob/master/packages/EasyStandard/src/Rector/PhpDocCommentRector.php

[6]: https://github.com/eonx-com/easy-monorepo/blob/master/packages/EasyStandard/src/Rector/RestoreDefaultNullToNullableTypeParameterRector.php

[7]: https://github.com/eonx-com/easy-monorepo/blob/master/packages/EasyStandard/src/Rector/SingleLineCommentRector.php

[8]: https://github.com/eonx-com/easy-monorepo/blob/master/packages/EasyStandard/src/Rector/StrictInArrayRector.php

[9]: https://github.com/eonx-com/easy-monorepo/blob/master/packages/EasyStandard/src/Rector/UselessSingleAnnotationRector.php
