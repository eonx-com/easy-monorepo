---eonx_docs---
title: 'AWS information'
weight: 1005
---eonx_docs---

# AWS information

You can include information about the AWS ECS Fargate task as metadata in Bugsnag reports. The AWS information is shown
on the *AWS* tab of Bugsnag.

Set the `aws_ecs_fargate.enabled` configuration to `true` to enable this feature (see [Configuration](config.md) for
more information).

Note that the `AwsEcsFargateConfigurator` must be also enabled (see [Client configurators](configurators.md)). The
`AwsEcsFargateConfigurator` automatically resolves information about the AWS ECS Fargate task (`AvailabilityZone`,
`Cluster`, `TaskARN` and `TaskDefinition`) and adds it as metadata to Bugsnag reports.

For custom AWS implementation, you can also set `aws_ecs_fargate.meta_url` for the URL to fetch AWS ECS Fargate task
metadata and `aws_ecs_fargate.meta_storage_filename` for the filename to cache AWS ECS Fargate task metadata into.
