---eonx_docs---
title: 'Session tracking'
weight: 1003
---eonx_docs---

# Session tracking

Bugsnag can track the number of **sessions** that happen in your application, which enables Bugsnag to provide stability
scores for comparison across releases of your application.

You can enable session tracking by simply setting the `session_tracking.enabled` configuration option to `true` (see
[Configuration](config.md)).

## Excluding URLs

Since you may not want every request to count towards a session, the EasyBugsnag package allows you to exclude specific
request URLs. For example, you might want to exclude the `/ping` URL from session tracking.

There are two configuration options that can help with excluding URLs from session tracking:

- `session_tracking.exclude_urls`: An array of URLs or regular expressions to exclude from session tracking. For
  example, you could use the URL `/ping` or the regular expression `(ping|pong)`. Note that all elements of the array
  are treated as regular expressions when matching URLs to exclude. Do not include regular expression delimiters in the
  elements of the array.
- `session_tracking.exclude_urls_delimiter`: Delimiter used in regular expressions for matching URLs to exclude from
  session tracking. By default, the delimiter is `#` but you should use another delimiter character if you want to
  exclude URLs that contain the `#` character.

## Cache configuration

In order to persist session information across individual requests, the EasyBugsnag package uses a cache. The package
uses a default cache implementation for both Symfony and Laravel frameworks, which uses files for caching.

The default cache expiry is set to one hour for performance reasons. If necessary, you can explicitly set the cache
expiry via the `session_tracking.cache_expires_after` configuration option.

You can tweak the default Symfony cache implementation through the following configuration options:

- `session_tracking.cache_directory`: Set the cache directory (the default is `%kernel.cache_dir%`).
- `session_tracking.cache_namespace`: Set the cache namespace (the default is `easy_bugsnag_sessions`).

You can tweak the default Laravel cache implementation through the following configuration option:

- `session_tracking.cache_store`: Set the cache store to use (the default is `file`).

### Custom cache implementation

You can even create your own cache implementation if required. For Symfony applications, create a cache that implements
`Symfony\Contracts\Cache\CacheInterface`. For Laravel applications, create a cache that implements
`Illuminate\Contracts\Cache\Repository`.

When you register your cache implementation as a service, use `\EonX\EasyBugsnag\Bundle\Enum\ConfigServiceId::SessionTrackingCache` as the service ID.

## Tracking queue jobs in Laravel

For Laravel applications, you can enable session tracking for queue jobs by setting the
`session_tracking.queue_job_count_for_sessions` configuration option to `true`.

## Tracking messages in Symfony

For Symfony applications, you can enable session tracking for messenger messages by setting the
`session_tracking.messenger_message_count_for_sessions.enbaled` configuration option to `true`.
