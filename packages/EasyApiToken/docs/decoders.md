---eonx_docs---
title: Decoders
weight: 1
---eonx_docs---

Let's have a look at the built-in decoders!

### Basic

This decoder will handle BasicAuth using the Authorization header and return a
`\EonX\EasyApiToken\Common\ValueObject\BasicAuth` instance. This interface allows you to retrieve
the username and password provided on the request.

<br>

### User ApiKey

This decoder will handle an ApiKey passed as the BasicAuth username in the Authorization header and return a
`\EonX\EasyApiToken\Common\ValueObject\ApiToken` instance.
This interface allows you to retrieve the ApiKey given on the request.

<p style="display: none">Required otherwise it breaks the warning format below</p>

::: warning
If a password is given as part of the BasicAuth this decoder will not consider it as an ApiKey.
:::

<br>

### JWT Header

This decoder will handle Bearer token using the Authorization header and return a
`\EonX\EasyApiToken\Common\ValueObject\Jwt` instance. This interface allows you to retrieve any
claim on the JWT, retrieve claims as array (useful for hash) and check the presence of a claim.

<br>

### JWT Param

This decoder will handle a JWT as query parameter, it will return the same instance as `JWT Header`.

<br>

### JWT Drivers

When using the JWT decoders, you will have to configure the underlying driver you want to use or even create your own.
By default, this package comes with 3 built-in drivers:

- Amazon Cognito: Allows you to decode JWT from [Amazon Cognito][1]. The following dependencies are required and need to be installed:
    - `composer require firebase/php-jwt:^6.5`
    - `composer require phpseclib/phpseclib:^3.0`
- Auth0: Allows you to decode JWT from [Auth0][2]. The following dependencies are required and need to be installed:
    - `composer require auth0/auth0-php:^8.6`
- Firebase: Allows you to decode JWT using the [Firebase PHP package][3]. The following dependencies are required and need to be installed:
    - `composer require firebase/php-jwt:^6.5`

[1]: https://aws.amazon.com/cognito/

[2]: https://auth0.com/

[3]: https://github.com/firebase/php-jwt
