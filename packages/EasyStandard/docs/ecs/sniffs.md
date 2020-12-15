---eonx_docs--- title: 'EasyStandard: Sniffs' weight: 1001 ---eonx_docs---

#### Arrays

##### [\EonX\EasyStandard\Sniffs\Arrays\OrderArrayKeysAlphabeticallySniff][1]

Arrays must be sorted by keys alphabetically.

```php
// incorrect
$array = ['z' => 1, 'g' => 2, 'a' => 3];
```

```php
// correct
$array = ['a' => 3, 'g' => 2, 'z' => 1];
```

**Parameters**

- `skipPatterns` - List of templates, if they match, the check is ignored. The type of token for which you want to
  check (function, class) is specified as the key. An array of patterns for checking the name is specified as the values
  of this key. By default, arrays inside functions whose name matches the pattern `/^provide.*/` are not checked.
  Default value: `[T_FUNCTION => ['/^provide.*/']]`.

##### [\EonX\EasyStandard\Sniffs\Classes\AvoidPublicPropertiesSniff][2]

Class properties must be protected or private.

```php
// incorrect
class MyClass
{
    public $myProperty1;
    // or
    $myProperty2;
}
```

```php
// correct
class MyClass
{
    private $myProperty1;
    // or
    protected $myProperty2;
}
```

##### [\EonX\EasyStandard\Sniffs\Classes\RequirePublicConstructorSniff][3]

Class constructor must be public.

```php
// incorrect
class MyClass
{
    protected function __construct()
    {
    }
}
```

```php
// correct
class MyClass
{
    public function __construct()
    {
    }
}
```

##### [\EonX\EasyStandard\Sniffs\Classes\RequireStrictDeclarationSniff][4]

Strict type declaration is required.

```php
// incorrect
<?php
// any php content
```

```php
// correct
<?php
declare(strict_types=1);

// any php content
```

##### [\EonX\EasyStandard\Sniffs\Classes\StrictDeclarationFormatSniff][5]

Strict type declaration must be on a new line with no leading whitespace.

```php
// incorrect
<?php

declare(strict_types=1);
// any php content
```

```php
// incorrect
<?php declare(strict_types=1);
// any php content
```

```php
// correct
<?php
declare(strict_types=1);

// any php content
```

#### Commenting

##### [\EonX\EasyStandard\Sniffs\Commenting\AnnotationSortingSniff][6]

Checks that annotations are sorted alphabetically.

```php
// incorrect
class MyClass
{
    /**
     * @return void
     *
     * @param mixed $withSomething
     */
    public function doSomething($withSomething): void
    {

    }
}
```

```php
// correct
class MyClass
{
    /**
     * @param mixed $withSomething
     *
     * @return void
     */
    public function doSomething($withSomething): void
    {

    }
}
```

**Parameters**

- `alwaysTopAnnotations` - A list of annotations that should always come first in the list, without regard to sorting.
  Default value: `[]`.

##### [\EonX\EasyStandard\Sniffs\Commenting\FunctionCommentSniff][7]

Checks that function comment blocks follow EonX standards.

```php
// incorrect
class MyClass
{
    /**
     * @return void
     *
     * @param mixed $withSomething
     */

    public function doSomethingA($withSomething): void
    {

    }

    /*
     * @return void
     *
     * @param string $withSomething
     */
    public function doSomethingB(string $withSomething): void
    {

    }

    public function doSomethingC(int $withSomething): void
    {

    }

    /**
     * Do something.
     *
     * @return void
     */
    public function doSomethingD(bool $withSomething): void
    {

    }
}
```

```php
// incorrect
class MyClass
{
    /**
     * Do something.
     *
     * @param mixed $withSomething
     *
     * @return void
     */
    public function doSomethingA($withSomething): void
    {

    }

    /**
     * Do something.
     *
     * @param string $withSomething
     *
     * @return void
     */
    public function doSomethingB(string $withSomething): void
    {

    }

    /**
     * Do something.
     *
     * @param int $withSomething
     *
     * @return void
     */
    public function doSomethingC(int $withSomething): void
    {

    }

    /**
     * Do something.
     *
     * @param bool $withSomething
     *
     * @return void
     */
    public function doSomethingD(bool $withSomething): void
    {

    }
}
```

#### Control Structures

##### [\EonX\EasyStandard\Sniffs\ControlStructures\ArrangeActAssertSniff][8]

Checks that a test method conforms to Arrange, Act and Assert (AAA) pattern. The allowed number of empty lines is
between [1, 2].

```php
// incorrect
final class TestClass
{
    public function testSomethingA()
    {
        $expectedResult = 4;
        $array = [
            'key' => 'value',
        ];
        $actualResult = 2 + 2;
        self::assertSame($expectedResult, $actualResult);
        self::assertSame(['key' => 'value'], $array);
    }

    public function testSomethingB()
    {
        $expectedResult = 4;
        $actualResult = 2 + 2;
        self::assertSame($expectedResult, $actualResult);
    }
}
```

```php
// correct
final class TestClass
{
    public function testSomethingA()
    {
        $expectedResult = 4;
        $array = [
            'key' => 'value',
        ];

        $actualResult = 2 + 2;

        self::assertSame($expectedResult, $actualResult);
        self::assertSame(['key' => 'value'], $array);
    }

    public function testSomethingB()
    {
        $actualResult = 2 + 2;

        self::assertSame(4, $actualResult);
    }

    public function testSomethingC()
    {
        self::assertSame(4, 2 + 2);
    }

    // Allow empty line in closure
    public function testSomethingD()
    {
        $value1 = 2;
        $value2 = 2;
        $expectedClosure = static function () use ($value1, $value2): int {
            $result = $value1 + $value2;

            return $result + 0;
        };

        $actualResult = 2 + 2;

        self::assertSame($expectedClosure(), $actualResult);
    }

    public function noTestMethod()
    {
        $expectedResult = 4;
        $actualResult = 2 + 2;
        self::assertSame($expectedResult, $actualResult);
    }
}
```

**Parameters**

- `testMethodPrefix` - If a method name starts with this prefix, checks will be applied to it. Default value: `test`.
- `testNamespace` - If a class namespace starts with this prefix, the class will be parsed. Default value: `App\Tests`.

```php
// correct
namespace App\NoTestNamespace;

final class TestClass
{
    public function testSomething()
    {
        $expectedResult = 4;

        $actualResult = 2 + 2;

        self::assertSame($expectedResult, $actualResult);

        echo $actualResult;
    }
}
```

##### [\EonX\EasyStandard\Sniffs\ControlStructures\NoNotOperatorSniff][9]

A strict comparison operator must be used instead of a NOT operator.

```php
// incorrect
$a = (bool)\random_int(0, 1);
if (!$a) {
    // Do something.
}
````

```php
// correct
$a = (bool)\random_int(0, 1);
if ($a === false) {
    // Do something.
}
````

#### Exceptions

##### [\EonX\EasyStandard\Sniffs\Exceptions\ThrowExceptionMessageSniff][10]

Exception message must be either a variable or a translation message, starting with a valid prefix.

```php
// incorrect
throw new \Exception('Incorrect message');
````

```php
// correct
throw new NotFoundHttpException();
// or
$exception = new Exception('Some exception message');
throw $exception;
// or
throw new InvalidArgumentException('exceptions.some_message');
// or
$message = 'Some exception message';
throw new RuntimeException($message);
````

**Parameters**

- `validPrefixes` - An array of prefixes that are valid for starting the message text. Default value: `['exceptions.']`.

#### Functions

##### [\EonX\EasyStandard\Sniffs\Functions\DisallowNonNullDefaultValueSniff][11]

Function and closure parameters can only have a default value of `null`.

```php
// incorrect
function someFunction(int $param1, ?stdClass $class, string $const3 = TestClass::TEST, array $param4 = []) {
    // No body needed
}
````

```php
// correct
function someFunction(int $param1, ?stdClass $class = null, ?string $const3 = null, ?array $param4 = null) {
    // No body needed
}
````

#### Methods

##### [\EonX\EasyStandard\Sniffs\Methods\TestMethodNameSniff][12]

Checks that a method name matches/does not match a specific regex.

**Parameters**

- `allowed` - An array of regular expressions to match method names. Default value:

```
[
    [
        'namespace' => '/^App\\\Tests\\\Unit/',
        'patterns' => ['/test[A-Z]/'],
    ],
]
```

- `forbidden` - An array of regular expressions that method names should not match. Default value:

```
[
    [
        'namespace' => '/^App\\\Tests\\\Unit/',
        'patterns' => ['/(Succeed|Return|Throw)[^s]/'],
    ],
]
```

#### Namespaces

##### [\EonX\EasyStandard\Sniffs\Namespaces\Psr4Sniff][13]

Checks that a namespace name matches PSR-4 project structure.

**Parameters**

- `composerJsonPath` - A relative path to the project file `composer.json`. Default value: `composer.json`.

[1]: https://github.com/eonx-com/easy-monorepo/blob/master/packages/EasyStandard/src/Sniffs/Arrays/OrderArrayKeysAlphabeticallySniff.php

[2]: https://github.com/eonx-com/easy-monorepo/blob/master/packages/EasyStandard/src/Sniffs/Classes/AvoidPublicPropertiesSniff.php

[3]: https://github.com/eonx-com/easy-monorepo/blob/master/packages/EasyStandard/src/Sniffs/Classes/RequirePublicConstructorSniff.php

[4]: https://github.com/eonx-com/easy-monorepo/blob/master/packages/EasyStandard/src/Sniffs/Classes/RequireStrictDeclarationSniff.php

[5]: https://github.com/eonx-com/easy-monorepo/blob/master/packages/EasyStandard/src/Sniffs/Classes/StrictDeclarationFormatSniff.php

[6]: https://github.com/eonx-com/easy-monorepo/blob/master/packages/EasyStandard/src/Sniffs/Commenting/AnnotationSortingSniff.php

[7]: https://github.com/eonx-com/easy-monorepo/blob/master/packages/EasyStandard/src/Sniffs/Commenting/FunctionCommentSniff.php

[8]: https://github.com/eonx-com/easy-monorepo/blob/master/packages/EasyStandard/src/Sniffs/ControlStructures/ArrangeActAssertSniff.php

[9]: https://github.com/eonx-com/easy-monorepo/blob/master/packages/EasyStandard/src/Sniffs/ControlStructures/NoNotOperatorSniff.php

[10]: https://github.com/eonx-com/easy-monorepo/blob/master/packages/EasyStandard/src/Sniffs/Exceptions/ThrowExceptionMessageSniff.php

[11]: https://github.com/eonx-com/easy-monorepo/blob/master/packages/EasyStandard/src/Sniffs/Functions/DisallowNonNullDefaultValueSniff.php

[12]: https://github.com/eonx-com/easy-monorepo/blob/master/packages/EasyStandard/src/Sniffs/Methods/TestMethodNameSniff.php

[13]: https://github.com/eonx-com/easy-monorepo/blob/master/packages/EasyStandard/src/Sniffs/Namespaces/Psr4Sniff.php
