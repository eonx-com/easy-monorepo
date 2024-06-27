---eonx_docs---
title: Logging Config In Symfony
weight: 1001
---eonx_docs---

The Symfony bundle of this package allows you to define your logging config providers and logger configurators anywhere
and use the container to register them to be used by the logger factory.

It requires to tag the different config providers and logger configurators as follows:

- **HandlerConfigProviderInterface:** `easy_logging.handler_config_provider`
- **ProcessorConfigProviderInterface:** `easy_logging.processor_config_provider`
- **LoggerConfiguratorInterface:** `easy_logging.logger_configurator`

By default, the bundle will register each interface listed below for auto-configuration and add the required
tag, so you have nothing to do.

However, if you need to tag services manually (e.g. 3rd party package), to make this process easier,
this package provides you with `\EonX\EasyLogging\Bundle\Enum\ConfigTag`.
