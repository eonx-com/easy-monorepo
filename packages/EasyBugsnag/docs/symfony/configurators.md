---eonx_docs---
title: Client Configurators In Symfony
weight: 1001
---eonx_docs---

To register additional `ClientConfigurators` in Symfony, you simply need to register a new service implementing the
`EonX\EasyBugsnag\Interfaces\ClientConfiguratorInterface` and you're done! This package register this interface
for auto-configuration by default, so you have nothing else to worry about. You're welcome!
