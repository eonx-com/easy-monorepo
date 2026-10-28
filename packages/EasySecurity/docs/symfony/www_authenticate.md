---eonx_docs---
title: WWW-Authenticate header
weight: 1003
---eonx_docs---

### WWW-Authenticate on 401 responses

[RFC 9110][1] requires a `WWW-Authenticate` header on every 401 response. The bundle adds it to any 401 response
of the main request that does not have one yet, whichever code built the response (the authenticator, an
exception handler, a controller).

The challenge is resolved from the token the configured API token decoder (`token_decoder`) finds in the request:

| Token | Header |
| --- | --- |
| JWT (`Authorization: Bearer ...`) | `Bearer` |
| API key (`Authorization: Basic ...` with an API key as the username) | `Basic realm="api-key"` |
| Username and password (`Authorization: Basic ...`) | `Basic realm="user"` |
| No token | `default_challenge`, `Bearer` by default |

### Configuration

```php
// config/packages/easy_security.php

use Symfony\Config\EasySecurityConfig;

return static function (EasySecurityConfig $easySecurityConfig): void {
    $easySecurityConfig->wwwAuthenticateHeader()
        // Enabled by default; set to false to turn the header off.
        ->enabled(true)
        // The challenge sent when the request has no token the decoder can read. Default: 'Bearer'.
        ->defaultChallenge('Bearer');
};
```

[1]: https://www.rfc-editor.org/rfc/rfc9110#section-15.5.2
