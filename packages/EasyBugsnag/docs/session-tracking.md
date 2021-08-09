---eonx_docs---
title: 'Session tracking'
weight: 1003
---eonx_docs---

# Session tracking

Bugsnag can track the number of **sessions** that happen in your application, which enables Bugsnag to provide stability
scores for comparison across releases of your application.

You can enable session tracking by simply setting the `session_tracking.enabled` configuration of EasyBugsnag to `true`
(see [Configuration](config.md)). In order to persist session information across individual requests, the EasyBugsnag
package uses a cache.

You can set the following to configure session tracking:
- `session_tracking.cache_expires_after`: Cache expiry. The default is set to one hour for performance reasons.
- `session_tracking.exclude_urls`: Array of URLs or regular expressions to exclude from session tracking.
- `session_tracking.exclude_urls_delimiter`: Delimiter used in regular expressions for resolving URLs to exclude from
  session tracking.

In Symfony, it is possible to customise the cache mechanism if required, by setting the following:
- `session_tracking.cache_directory`: Set the cache directory.
- `session_trackingcache_namespace`: Set the cache namespace (the default is `easy_bugsnag_sessions`). You can override
  the default cache implementation by using a cache that implements the `Symfony\Contracts\Cache\CacheInterface` with
  the relevant namespace.

In addition, `session_tracking.queue_job_count_for_sessions` will enable session tracking for queue jobs for Laravel
applications. For Symfony applications, `session_tracking.messenger_message_count_for_sessions` will enable session
tracking for messenger messages.
