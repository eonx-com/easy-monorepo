# EonX Symfony Recipes

This repository is to be used as a [Symfony Flex][0] endpoint for EonX Symfony projects as it contains custom recipes.

## Usage

To use this repository as a Symfony Flex endpoint in your project, you can either add the URL of this repository 
in the `extra.symfony.endpoint` config option of `composer.json` or in the `SYMFONY_ENDPOINT` env var.
For more information, please refer to this [article][1].

The URL to use for this repository is:

```
https://api.github.com/repos/eonx-com/symfony-recipes/contents/index.json?ref=flex/main
```

### Composer.json

```json
{
    "extra": {
        "symfony": {
            "allow-contrib": false,
            "require": "5.3.*",
            "endpoint": "https://api.github.com/repos/eonx-com/symfony-recipes/contents/index.json?ref=flex/main"
        }
    }
}
```

[0]: https://github.com/symfony/flex
[1]: https://symfony.com/blog/symfony-flex-is-going-serverless
