---eonx_docs---
title: 'Worker information'
weight: 1004
---eonx_docs---

# Worker information

For Symfony applications, you can include information about the worker as metadata in Bugsnag reports. The worker
information is shown on the *Worker* tab of Bugsnag.

Set the `worker_info.enabled` configuration to `true` to enable this feature (see [Configuration](config.md) for more
information).

The following worker information is added as metadata in Bugsnag reports: `Message`, `Receiver Name`, `Received At` and
`Stamps`.
