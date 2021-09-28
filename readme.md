---eonx_docs---
title: Documentation
weight: 0
---eonx_docs---

This repository is a mono-repository, it contains the code of many packages. For more information about this concept
you can look [there][1].


<br>

### Packages

<!-- monorepo-packages -->
- [EasyApiToken](https://github.com/eonx-com/easy-api-token): Make your API accept multiple authentication strategies in a convenient way (Basic, ApiKey, JWT, ...)
- [EasyAsync](https://github.com/eonx-com/easy-async): Makes dealing with async processes easier
- [EasyAwsCredentialsFinder](https://github.com/eonx-com/easy-aws-credentials-finder): Tool to ease finding aws credentials in project/machine
- [EasyBankFiles](https://github.com/eonx-com/easy-bank-files): Parsers/Generators for common bank files
- [EasyBatch](https://github.com/eonx-com/easy-batch): Batching async processes made easy
- [EasyBugsnag](https://github.com/eonx-com/easy-bugsnag): Ease integrating bugsnag in your PHP projects
- [EasyCore](https://github.com/eonx-com/easy-core): Provides core features for Lumen applications
- [EasyDecision](https://github.com/eonx-com/easy-decision): Your most complex decisions taken the easiest way
- [EasyErrorHandler](https://github.com/eonx-com/easy-error-handler): Provides customizable ready-to-use error handler for applications
- [EasyEventDispatcher](https://github.com/eonx-com/easy-event-dispatcher): Framework agnostic event dispatcher
- [EasyHttpClient](https://github.com/eonx-com/easy-http-client): Utils around HTTP client
- [EasyLock](https://github.com/eonx-com/easy-lock): Framework agnostic locking features
- [EasyLogging](https://github.com/eonx-com/easy-logging): Create and configure Monolog Loggers easily
- [EasyNotification](https://github.com/eonx-com/easy-notification): Client for dispatching notifications at EonX
- [EasyPagination](https://github.com/eonx-com/easy-pagination): Provides a generic way to handle pagination data from clients
- [EasyPipeline](https://github.com/eonx-com/easy-pipeline): Provides an easy and powerful way to implement pipelines for anything
- [EasyPsr7Factory](https://github.com/eonx-com/easy-psr7-factory): Provides an easy way to create PSR7 Request/Response from Symfony Request/Response
- [EasyRandom](https://github.com/eonx-com/easy-random): Provides easy way to generate random values (string, int, uuids, ...)
- [EasyRepository](https://github.com/eonx-com/easy-repository): Provides an easy way to implement the Repository Design Pattern in your applications
- [EasyRequestId](https://github.com/eonx-com/easy-request-id): Uniquely identify each request across multiple projects
- [EasySchedule](https://github.com/eonx-com/easy-schedule): Provides the Command Scheduling logic of Laravel in a Symfony Console application
- [EasySecurity](https://github.com/eonx-com/easy-security): Provides security features to be generic across applications
- [EasySsm](https://github.com/eonx-com/easy-ssm): CLI tool to interact with AWS ParameterStore in a convenient way
- [EasyTest](https://github.com/eonx-com/easy-test): Makes testing easier
- [EasyUtils](https://github.com/eonx-com/easy-utils): EonX packages utils
- [EasyWebhook](https://github.com/eonx-com/easy-webhook): Sending webhooks has never been so easy (persistence, retry, async)
<!-- end-monorepo-packages -->

<br>

### Contribute

- Any new feature and/or hotfix MUST be submitted as a PR
- The title of the PR MUST respect the following pattern `[<PackageName>] <PR Title>`
- The PR MUST pass the checks before being merged
- (Ideally) Create one PR per package

<br>

### Release New Version

The release of a new version MUST be done from the `master` branch.

```bash
# 1. Make sure to pull the latest version of master in your local copy of the repository
$ git checkout master && git pull

# Generate changelog. Make sure to verify the content of CHANGELOG.md after each run
# Release the new version
# Split new version to each package
$ composer release vX.X.X
```

[1]: https://en.wikipedia.org/wiki/Monorepo
