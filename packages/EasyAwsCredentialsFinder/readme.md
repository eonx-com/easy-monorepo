---eonx_docs---
title: Introduction
weight: 0
---eonx_docs---

### Require package (Composer)

The recommended way to install this package is to use [Composer][1]:

```bash
$ composer require eonx-com/easy-aws-credentials-finder
```

### How it works

This package will look for AWS credentials on the current host and make them available to your PHP code.

Here is the list of different strategies supported:

- AWS CLI SSO cache: will lookup the cached SSO credentials for current profile (Requires aws cli v2 installed)

[1]: https://getcomposer.org/
