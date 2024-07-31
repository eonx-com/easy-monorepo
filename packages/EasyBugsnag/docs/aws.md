---eonx_docs---
title: 'AWS ECS Fargate information'
weight: 1005
---eonx_docs---

# AWS ECS Fargate information

If required, you can include information about your application's AWS ECS Fargate task as metadata in Bugsnag reports.
The AWS ECS Fargate information is shown on the *AWS* tab of Bugsnag.

:::tip
The EasyBugsnag package currently only supports AWS ECS Fargate instances.
:::

Set the `aws_ecs_fargate.enabled` configuration option to `true` to enable this feature (see [Configuration](config.md)
for more information).

Note that the default configurators (which includes the `AwsEcsFargateClientConfigurator`) must also be enabled by setting the
`use_default_configurators` configuration option to `true`.

The `AwsEcsFargateConfigurator` automatically resolves information about the AWS ECS Fargate task (`AvailabilityZone`,
`Cluster`, `TaskARN` and `TaskDefinition`) and adds it as metadata to Bugsnag reports.

For custom AWS implementation, you can also set `aws_ecs_fargate.meta_url` for the URL to fetch AWS ECS Fargate task
metadata and `aws_ecs_fargate.meta_storage_filename` for the filename to cache AWS ECS Fargate task metadata into.
