---eonx_docs---
title: Configuration
weight: 1001
---eonx_docs---

### Create the configuration file

This package defines the `easy_api_token` extension, so to implement your own configuration simply create the following
file:

```yaml
# config/packages/easy_api_token.yaml

easy_api_token:
    # Your configuration here...
```

### Configure decoders

Define your decoders under the `decoders` key in your configuration as:

```yaml
# config/packages/easy_api_token.yaml

easy_api_token:
    decoders:
        basic: null # Built-in decoder for BasicAuth
        user-apikey: null # Built-in decoder for ApiKey
```

### Built-in Decoders

This package comes with built-in decoders, let's see which ones and how to configure them.

```yaml
# config/packages/easy_api_token.yaml

easy_api_token:
    decoders:
        chain:
            list:
                - basic
                - user-apikey
                - jwt-header

        basic: null
        user-apikey: null
        jwt-header:
            driver: auth0
            options:
                cache_path: path/to/cache # Optional
                valid_audiences: ['id1', 'id2']
                authorized_iss: ['xyz.auth0', 'abc.goog']
                private_key: someprivatekeystring # Required only for HS256
                allowed_algos: ['HS256', 'RS256']
        jwt-param:
            driver: firebase
            options:
                algo: HS256
                allowed_algos: ['HS256', 'RS256']
                leeway: 15
                param: authParam
                private_key: someprivatekeystring
                public_key: somepublickeystring
```
