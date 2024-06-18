---eonx_docs---
title: 'Client factory'
weight: 1001
---eonx_docs---

# Client factory

The core functionality of the EasyBugsnag package is to create a Bugsnag client instance and make it available to your
application, so you can focus on notifying your errors/exceptions instead of the boilerplate Bugsnag setup. It uses a
**client factory** to do this.

This factory implements `EonX\EasyBugsnag\Factory\ClientFactoryInterface` which is able to create the client from
just your Bugsnag Integration API key.

However, if needed you can set your own implementations of the following additional objects used by the Bugsnag client:

- `HttpClient`: HTTP client used to send notifications to Bugsnag.
- `RequestResolver`: used to resolve request information for sending to Bugsnag.
- `ShutdownStrategy`: used for determining when to send notifications to Bugsnag.

## HTTP client

By default, the Bugsnag client uses the [Guzzle HTTP client][1] to send notifications to Bugsnag. You can create your
own HTTP client that implements `GuzzleHttp\ClientInterface` and set the Bugsnag client to use it instead via the client
factory's `setHttpClient()` method.

## Request resolver

The Bugsnag client's **request resolver** determines information about the request that triggered the error or exception
in your application, such as the request's method and headers.

By default, the EasyBugsnag package uses a framework-specific request resolver for the Bugsnag client. Thus Symfony uses
`EonX\EasyBugsnag\Resolver\SymfonyRequestResolver` and Laravel uses
`EonX\EasyBugsnag\Laravel\Resolvers\LaravelRequestResolver`.

If required, you can create your own request resolver that implements `Bugsnag\Request\ResolverInterface` and set the
Bugsnag client to use it via the client factory's `setRequestResolver()` method.

## Shutdown strategy

By default, the Bugsnag client batches the sending of notifications to Bugsnag. The strategy for when to send
notifications is defined by the Bugsnag client's **shutdown strategy**.

The EasyBugsnag package is configured to execute the Bugsnag client's shutdown strategy when triggered by the following
events:

- application terminate
- console terminate
- worker running a new job

The default EasyBugsnag shutdown strategy (`EonX\EasyBugsnag\Strategy\ShutdownStrategy`) will call `flush()` and
`clearBreadcrumbs()` on the Bugsnag client.

You can create your own shutdown strategy that implements `Bugsnag\Shutdown\ShutdownStrategyInterface` and set the
Bugsnag client to use it instead via the client factory's `setShutdownStrategy()` method.

[1]: http://docs.guzzlephp.org/en/stable/
