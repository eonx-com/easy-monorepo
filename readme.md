---eonx_docs---
title: Documentation
weight: 0
---eonx_docs---

This repository is a mono-repository, it contains the code of many packages. For more information about this concept
you can look [there][1].

<br>

### Packages

- [EasyApiToken](https://github.com/eonx-com/easy-api-token): Tools to handle API tokens like a pro.
- [EasyAsync](https://github.com/eonx-com/easy-async): Makes dealing with async processes easier.
- [EasyCfhighlander](https://github.com/eonx-com/easy-cfhighlander): CLI tool to generate Cfhighlander templates.
- [EasyDecision](https://github.com/eonx-com/easy-decision): Your most complex decisions made the easiest way.
- [EasyDocker](https://github.com/eonx-com/easy-docker): CLI tool to generate Docker files.
- [EasyEntityChange](https://github.com/eonx-com/easy-entity-change): Provides an easy way to hook up logic in your entities lifecycle.
- [EasyErrorHandler](https://github.com/eonx-com/easy-error-handler): Provides customizable ready-to-use error handler for applications.
- [EasyIdentity](https://github.com/eonx-com/easy-identity): Tools to handle authentication like a pro.
- [EasyPipeline](https://github.com/eonx-com/easy-pipeline): An easy way to implement the Pipeline Design Pattern in your applications.
- [EasyRepository](https://github.com/eonx-com/easy-repository): An easy way to implement the Repository Design Pattern in your applications.
- [EasyPagination](https://github.com/eonx-com/easy-pagination): A generic way to handle pagination data from clients.
- [EasyPsr7Factory](https://github.com/eonx-com/easy-psr7-factory): An easy way to create PSR7 Request/Response from Symfony Request/Response.
- [EasySchedule](https://github.com/eonx-com/easy-schedule): Provides the Command Scheduling logic of Laravel in a Symfony Console application.
- [EasySecurity](https://github.com/eonx-com/easy-security): Provides security features to be generic across applications.
- [EasySsm](https://github.com/eonx-com/easy-ssm): CLI tool to interact with AWS ParameterStore in a convenient way.
- [EasyStandard](https://github.com/eonx-com/easy-standard): Centralised source of coding standard classes.
- [EasyTest](https://github.com/eonx-com/easy-test): Makes testing easier.

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

# 2. Generate changelog. Make sure to verify the content of CHANGELOG.md after each run
$ composer changelog

# 3. Release the new version
$ vendor/bin/monorepo-builder release vX.X.X

# 4. Split new version to each package
$ composer split
```

[1]: https://en.wikipedia.org/wiki/Monorepo
