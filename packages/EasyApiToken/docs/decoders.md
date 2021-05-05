---eonx_docs---
title: Decoders
weight: 1
---eonx_docs---

Let's have a look at the built-in decoders!

### Basic

This decoder will handle BasicAuth using the Authorization header and return a
`EonX\EasyApiToken\Interfaces\Tokens\BasicAuthEasyApiTokenInterface` instance. This interface allows you to retrieve
the username and password provided on the request.

<br>

### User ApiKey

This decoder will handle an ApiKey passed as the BasicAuth username in the Authorization header and return a
`EonX\EasyApiToken\Interfaces\Tokens\ApiKeyEasyApiTokenInterface` instance.
This interface allows you to retrieve the ApiKey given on the request.

<p style="display: none">Required otherwise it breaks the warning format below</p>

::: warning
If a password is given as part of the BasicAuth this decoder will not consider it as an ApiKey.
:::

<br>

### JWT Header

This decoder will handle Bearer token using the Authorization header and return a
`EonX\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface` instance. This interface allows you to retrieve any
claim on the JWT, retrieve claims as array (useful for hash) and check the presence of a claim.

<br>

### JWT Param

This decoder will handle a JWT as query parameter, it will return the same instance as `JWT Header`.

<br>

### JWT Drivers

When using the JWT decoders, you will have to configure the underlying driver you want to use or even create your own.
By default, this package comes with 2 built-in drivers:

- Auth0: Allows you to decode JWT from [Auth0][1]
- Firebase: Allows you to decode JWT using the [Firebase PHP package][2]

[1]: https://auth0.com/
[2]: https://github.com/firebase/php-jwt
