---eonx_docs---
title: 'Error response format'
weight: 1006
---eonx_docs---

# Error response format

By default, the EasyErrorHandler formats HTTP error response bodies as JSON. You can create your own custom error
response formatter by implementing `EonX\EasyErrorHandler\Common\Factory\ErrorResponseFactoryInterface` and overriding the
interface in your application's service container.
