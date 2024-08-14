# Contributing

Everybody is welcome to contribute to this project. 

## New Features or Ideas

If you believe that you have an amazing feature you want to provide us all with, please create an issue and/or a pull request.

## Bugs

If you believe that you found a bug (yes it happens sometimes), don't hesitate to create an issue and/or a pull request as 
well.

## Architecture Conventions

The code of this project follows the architecture rules listed below. All the changes and new features MUST follow these rules.

### Common Rules

- All the packages are structured using the "packaging by type inside packaging by feature" principle.
  - Every nesting level of directories MUST represent either "features" (logical modules) or "types" (service/class types).
  - If a package doesn't contain multiple "features", you MUST put "types" on the first nesting level in the `src` directory.
  - But if you decide to split the package code into logical modules, you MUST name the directories of the first nesting level
    as "features" and put "types" on the next nesting level.
  - A package with multiple "features" will usually have general-purpose classes or classes common for other modules. Such
    classes MUST reside in the `Common` module.
  - All the modules containing logic for integrating other packages SHOULD be named according to the integrated package name.
  - The "feature" and "type" directories MUST NOT be mixed, and only "type" directories CAN have nested files.
- You SHOULD always try to find a commonly used "type" for each class you create. This can be a term widely used in PHP itself
  (like `Exception`, `Repository`, `ValueObject`, etc.) or in packages/frameworks you use (like `Entity` in Doctrine,
  `ApiResource` in API Platform, `EventListener` in Symfony, etc.). If you have to invent your own "type", try to find a
  non-broad, concrete, and clear name, ideally containing only one word.
- All the service classes MUST be suffixed with their parent directory name (i.e. the "type"). This rule applies to services
  and not object classes (e.g. Entity, Enum, ValueObject, etc.).
- Never name your services using the `Decorator` suffix, because every decorator represents the same service "type" that the
  decorated class.
- Interfaces and Traits MUST always be placed in the same directory where their implementations or consumer classes reside.
- There is a specific `Helper` class type that usually contains static methods or is utilized by one service to delegate some
  specific functionality to this Helper. You MUST name such classes using the `Helper` suffix and parent directory name. All
  the Interfaces and Traits used by such a Helper MUST reside in the same directory.
- You SHOULD always try to find a self-describing speaking name for services you create. Having too broad names (like
  `EntityHandler`, `ProcessingService`, `TransactionManager`, etc.) makes the code less understandable and intuitive.
- All the directories and files inside the `src` directory MUST be named using PascalCase and MUST NOT be named with all
  UPPERCASED letters.

### Symfony-specific Rules

- All the bundle classes MUST be named as `<PackageName>Bundle`.
- All the directories MUST be named in singular form.
- All the files not representing classes MUST be named using snake_case.

### Laravel-specific Rules

- All the Service Provider classes MUST be named as `<PackageName>ServiceProvider`.
- All the directories MUST be named in plural form.
- All the files not representing classes MUST be named using kebab-case.
