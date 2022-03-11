---eonx_docs---
title: Introduction
weight: 0
---eonx_docs---

It is common to restrict access to the endpoints of an API by implementing an authentication mechanism.
To do so, you have a lot of available solutions: Basic Auth, API keys, JWTs, etc.
Which one to pick?
There is no magic solution, and you should make your decision based on the specific
problem you're trying to solve.


This package will decode the incoming HTTP Request to extract the "ApiToken" as a PHP object that your application can
then use. It comes with built-in decoders for:
- Basic Auth
- API keys
- JWTs (Amazon Cognito, Auth0, Firebase)

So you can start supporting each of those strategies with no effort. It also provides a "chain" decoder allowing you
to support multiple ApiToken strategies for the same API endpoint.


Each ApiToken implements a common interface allowing you to use them as parameters regardless of the ApiToken strategy
used, also each ApiToken has its own specific PHP class allowing you to implement business logic based on the ApiToken
strategy used.

<br>

### Require package (Composer)

The recommended way to install this package is to use [Composer][1]:

```bash
$ composer require eonx-com/easy-api-token
```

[1]: https://getcomposer.org/
