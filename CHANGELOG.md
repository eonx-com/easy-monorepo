<!-- changelog-linker -->

<!-- dumped content start -->

## v2.3.8 - 2020-04-02

### EasyDecision

- [#188] Implement restricted decision configurator

### EasyPagination

- [#191] Use psr7factory to create server request in laravel

### EasyStandards

- [#189] Implement sniff to avoid public properties in classes

<!-- dumped content end -->

<!-- dumped content start -->

## [v2.3.7] - 2020-04-01

- [#181] Update dependencies to work with Symfony 5

### EasyPagination

- [#162] Add eloquent length aware paginator, Thanks to [@cainseing]

### EasyCore

- [#180] Enhance api platform
- [#176] Auto configure doctrine listeners
- [#174] Symfony bridge auto configure listeners
- [#173] No IRI item for api platform

### EasyDecision

- [#183] Implement Collector pattern to improve DX

### EasyLogging

- [#182] setApp method registers the EasyLogging interface, Thanks to [@egor-dev]

<!-- dumped content end -->

<!-- dumped content start -->

## [v2.3.6] - 2020-03-20

### EasyStandard

- [#172] Add missing sniffs from natepage/sniffs

<!-- dumped content end -->

<!-- dumped content start -->

## [v2.3.5] - 2020-03-19

### EasyStandard

- [#171] Create package

<!-- dumped content end -->

<!-- dumped content start -->

## [v2.3.4] - 2020-03-19

### EasyPagination

- [#169] Create Symfony bridge

<!-- dumped content end -->

<!-- dumped content start -->

## [v2.3.3] - 2020-03-19

- [#164] Configure WhiteSource Bolt for GitHub, Thanks to [@whitesource-bolt-for-github][bot]
- [#159] Make sure main packages are running on PHP7.1 to 7.4

### EasyApiToken

- [#166] Implement getClaimForceArray on JwtEasyApiToken

### EasyAsync

- [#157] Implement messenger middleware for process job log

### EasyCore

- [#158] Implement messenger middleware for process with lock

### EasySecurity

- [#167] Make claim a config instead of constant

### EasyApiToken

- [#163] Remove deprecated code

### EasyPsr7Factory

- [#160] Remove deprecated code

### Unknown Package

- [#161] Remove var directory created by Symfony during tests

<!-- dumped content end -->

<!-- dumped content start -->

## [v2.3.2] - 2020-03-10

- [#151] Update coding standards

### EasyDecision

- [#148] Make adding extra in output consistent

### EasyAsync

- [#149] Persist jobLog when status is in progress, Thanks to [@dextercampos]

### EasyCore

- [#155] Migrate core bundle for Symfony

### EasySchedule

- [#156] Created package

### EasySecurity

- [#152] Allow to resolve provider from multiple headers

### EasyTest

- [#147] Prepare for multi coverage formats

<!-- dumped content end -->

<!-- dumped content start -->

## [v2.3.1] - 2020-03-03

### EasyApiToken

- [#145] Allow caching for JWKs retrieval, Thanks to [@edwardmurrell-loyaltycorp]

<!-- dumped content end -->

<!-- dumped content start -->

## [v2.3.0] - 2020-03-02

### EasyAsync

- [#143] Create package

### EasyPagination

- [#143] Add Doctrine ORM and DBAL paginators

<!-- dumped content end -->

<!-- dumped content start -->

## [v2.2.1] - 2020-02-28

### EasyTest

- [#140] Require nette/utils, Thanks to [@ttam]

<!-- dumped content end -->

<!-- dumped content start -->

## [v2.2.0] - 2020-02-23

- [#136] Check code coverage as part of CI
- [#135] Organise phpstan.neon by packages

### EasyCore

- [#134] Feature/clean up lumen cached config
- [#133] Use env method in config

### EasyPagination

- [#137] Improvements for apps to allow "manual" pagination
- [#138] Implement better length aware paginator for doctrine orm

### EasyRepository

- [#138] Implement better length aware paginator for doctrine orm

<!-- dumped content end -->

<!-- dumped content start -->

## [v2.1.4] - 2020-02-17

### EasyDecision

- [#132] Rules extra in output

<!-- dumped content end -->

## [v2.1.3] - 2020-02-16

- Fix path to easy-core config file

<!-- dumped content start -->

## [v2.1.2] - 2020-02-16

### EasyCore

- [#131] Simplify usage

### EasyLogging

- [#130] Logger also replace Psr LoggerInterface in app

<!-- dumped content end -->

<!-- dumped content start -->

## [v2.1.1] - 2020-02-16

### EasyLogging

- [#128] Skip easy-logging package itself in introspection

### EasyCore

- [#129] Improve laravel jobs with listeners

<!-- dumped content end -->

<!-- dumped content start -->

## [v2.1.0] - 2020-02-15

- [#126] Feature/upgrade symplify packages

### EasySecurity

- [#127] Create package

### EasyTest

- [#125] Create package

<!-- dumped content end -->

<!-- dumped content start -->

## [v2.0.10] - 2020-02-09

### EasyEntityChange

- [#124] Ensure inserts are reset on each flush, Thanks to [@merk]

<!-- dumped content end -->

<!-- dumped content start -->

## [v2.0.9] - 2020-02-06

### EasyDecision

- [#123] Do not cache decision in factory

<!-- dumped content end -->

<!-- dumped content start -->

## [v2.0.8] - 2020-02-06

### EasyDecision

- [#122] Implement optional name on expression language rule

<!-- dumped content end -->

<!-- dumped content start -->

## [v2.0.7] - 2020-02-05

### EasyDecision

- [#121] Fix context exception when decision with no rules

<!-- dumped content end -->

<!-- dumped content start -->

## [v2.0.6] - 2020-02-05

### EasyDecision

- [#120] Implement default output when no rules provided

<!-- dumped content end -->

<!-- dumped content start -->

## [v2.0.5] - 2020-01-29

### EasySecurity

- [#119] Make context abstract as each app must define their own context

<!-- dumped content end -->

<!-- dumped content start -->

## [v2.0.4] - 2020-01-30

- [#117] Clean-up dependencies versions

### EasyApiToken

- [#116] Allow to retrieve original token

### EasySecurity

- [#118] Create package

<!-- dumped content end -->

<!-- dumped content start -->

## [v2.0.3] - 2020-01-17

- [#114] Feature/clean up repo

### EasyDecision

- [#113] POIN-426-update-available-expressions-spelling, Thanks to [@skrut]

### EasyLogging

- [#115] Check shouldReport if the exception is throwable, Thanks to [@merk]

<!-- dumped content end -->

<!-- dumped content start -->

## [v2.0.2] - 2020-01-14

### EasyLogging

- [#112] Fix signature for JsonFormatter::format()

<!-- dumped content end -->

<!-- dumped content start -->

## [v2.0.1] - 2020-01-14

### EasyLogging

- [#111] Fix formatter format signature

<!-- dumped content end -->

<!-- dumped content start -->

## [v2.0.0] - 2020-01-09

### Search

- [#110] BC BREAK Entity Change now tracks changed properties, Thanks to [@merk]

<!-- dumped content end -->

<!-- dumped content start -->

## [v1.0.0] - 2019-12-11

- [#102] Migrate to EonX namespaces

## [v0.11.2] - 2019-12-16

### EasyEntityChange

- [#109] Add a watchlist to EntityChangeSubscriber

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.11.1] - 2019-12-15

- Bump utils version to ^1.0

## [v0.11.0] - 2019-12-11

- [#105] Update dependencies to eonx-com, Thanks to [@roman-loyaltycorp]

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.10.11] - 2019-12-11

### EasyEntityChange

- [#106] Skip job dispatches for empty jobs, Thanks to [@merk]

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.10.10] - 2019-12-10

### EasyLogging

- [#104] Replace eoneopay/externals with eonx-com/externals, Thanks to [@roman-loyaltycorp]

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.10.9] - 2019-11-26

### EasyLogging

- [#101] Fix autoload for easy-logging in composer.json

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.10.8] - 2019-11-26

### EasyLogging

- [#100] Create package to centralise common logging logic

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.10.7] - 2019-11-13

### EasyCfhighlander

- [#99] Update cfhighlander versions in cloudformation templates

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.10.6] - 2019-11-07

### EasyEntityChange

- [#98] Preserving entity id for use at postFlush, Thanks to [@olamedia]

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.10.5] - 2019-10-22

### EasyDocker

- [#97] Fix elasticsearch config in docker-compose.yml.twig, Thanks to [@roman-loyaltycorp]

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.10.4] - 2019-10-18

### EasyDocker

- [#95] Implement elasticsearch in docker templates

### EasyEntityChange

- [#96] Create package

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.10.3] - 2019-09-30

### EasyDecision

- [#93] Make sure value decision always return at least the input value

## [v0.10.2] - 2019-09-25

### EasyCore

- [#91] lumen configuration cache, Thanks to [@olamedia]

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.10.1] - 2019-09-21

### EasyDecision

- [#92] Add non blocking error handling

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.10.0] - 2019-09-19

### EasyIdentity

- [#90] Remove state from identity, Thanks to [@sjdaws]

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.9.4] - 2019-09-05

### EasyApiToken

- [#89] Update Auth0JwtDriver to accept roles custom claim, Thanks to [@rashmitsingh]

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.9.3] - 2019-08-26

### EasyCfhighlander

- [#87] Fix indent in project.config.yaml for code templates

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.9.2] - 2019-08-26

### EasyCfhighlander

- [#86] Use aurora 2.0.4 because the semver syntax is broken
- [#85] Pass ecs params to cloudformation only when cli enabled

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.9.1] - 2019-08-23

### EasyCfhighlander

- [#83] Sync template + CLI service

### EasyDocker

- [#84] Make sure to use cached params first

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.9.0] - 2019-08-22

- [#77] Fix autoload paths issue, Thanks to [@olamedia]

### EasyCfhighlander

- [#80] Fix file overwrite issue, Thanks to [@olamedia]
- [#81] Fix CLI params missing

### EasyDocker

- [#81] Fix CLI params missing
- [#79] Fix file overwrite issue, Thanks to [@olamedia]

### EasyRepository

- [#82] Database repository interface

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.8.3] - 2019-08-05

- [#70] Updated GitHub PR Template to denote CHANGELOG does not actually require, Thanks to [@MitchellMacpherson]

### EasyCfhighlander

- [#73] Update ELASTICSEARCH_HOST to append https:// to $ElasticSearchHost, Thanks to [@merk]
- [#69] Use .easy directory for easy-docker generated parameter & manifest files, Thanks to [@MitchellMacpherson]
- [#67] Make sure project has "-backend" in the name for Cloudformation
- [#71] Added comments to template cfhighlander files to explain file purpose, Thanks to [@MitchellMacpherson]
- [#78] Fix autoload paths issue, Thanks to [@olamedia]

### EasyDocker

- [#76] Check composer install with official signature
- [#72] Support for hirak/prestissimo in api Dockerfile, Thanks to [@MitchellMacpherson]
- [#68] Use .easy directory for easy-docker generated parameter & manifest files, Thanks to [@MitchellMacpherson]
- [#77] Fix autoload paths issue, Thanks to [@olamedia]
- [#75] Remove NewRelic

### EasyIdentity

- [#74] Avoid exceptions when empty URL for Auth0, Thanks to [@rashmitsingh]

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.8.2] - 2019-07-05

### EasyCfhighlander

- [#66] Fix templates whitespace + nginx ports for FARGATE

### EasyDocker

- [#65] Remove double brackets in migrate.sh

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.8.1] - 2019-07-03

### EasyDocker

- [#64] Change sh to bash

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.8.0] - 2019-07-02

### EasyApiToken

- [#62] Use custom token generator for auth0 jwt token creation, Thanks to [@ketanp77]

### EasyDecision

- [#58] Allow additional parameters when creating decision, Thanks to [@albusss]
- [#59] Fix scaling issues and make decision engine easier to extend
- [#63] Reverse priority order for decision rules, Thanks to [@egor-dev]

### EasyCfhighlander

- [#55] Better parameters resolvers
- [#53] Update code templates to latest (Fargate, ecs, ...)
- [#61] Update templates to work with FARGATE

### EasyDocker

- [#56] Better parameter resolvers
- [#54] nginx improvements, Thanks to [@merk]
- [#60] Update templates to work with FARGATE

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.7.11] - 2019-06-17

### EasyDocker

- [#52] Fix cron permissions + query string in nginx + queue driver to redis

<!-- dumped content end -->

## [v0.7.10] - 2019-06-17

### EasyDocker

- Fix nginx default config with backslash

<!-- dumped content start -->

## [v0.7.9] - 2019-06-14

### EasyCfhighlander

- Fix dependencies

## [v0.7.8] - 2019-06-14

### EasyCfhighlander

- [#51] Update templates

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.7.7] - 2019-06-14

### EasyCfhighlander

- [#50] Remove cwd from filename in manifest

### EasyDocker

- [#49] Remove cwd from filename in manifest

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.7.6] - 2019-06-14

### EasyDocker

- [#47] Fix generated files permissions + cached params
- [#48] Improve files generation and structure

### EasyCfhighlander

- [#46] Fix files permissions and cached params

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.7.5] - 2019-06-13

### EasyCfhighlander

- [#45] Improve CLIs params cache

### EasyDocker

- [#44] fix bug with newrelic skip, Thanks to [@bradmathews]
- [#45] Improve CLIs params cache

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.7.4] - 2019-06-12

### EasyDocker

- [#43] Update cron user permissions to fix logging, Thanks to [@bradmathews]

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.7.3] - 2019-06-07

### EasyDocker

- [#42] Nginx conf file and migrate container dependencies, Thanks to [@bradmathews]

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.7.2] - 2019-06-07

### EasyDocker

- [#41] Fix nginx conf to listen to port 80 instead of 8080

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.7.1] - 2019-06-07

### EasyDocker

- [#40] Improve docker files templates

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.7.0] - 2019-05-31

### EasyCfhighlander

- [#38] Add simple documentation
- [#39] Update bin name for consistency

### EasyDocker

- [#37] Add simple documentation

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.6.14] - 2019-05-31

### EasyCfhighlander

- [#36] Implement status and manifest concept

### EasyDocker

- [#35] Create package

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.6.13] - 2019-05-31

### EasyDecision

- [#34] Delegate middleware class definition to concrete decision

<!-- dumped content end -->

## [v0.6.12] - 2019-05-16

### EasyCfhighlander

- Fix ecr_repo in code templates

## [v0.6.11] - 2019-05-16

### EasyCfhighlander

- Add alpha validator + db_username

## [v0.6.10] - 2019-05-16

### EasyCfhighlander

- Allow database name to be different than project name

## [v0.6.9] - 2019-05-15

### EasyCfhighlander

- Update cloudformation branch to develop

<!-- dumped content start -->

## [v0.6.8] - 2019-05-15

### EasyCfhighlander

- [#33] Update templates to last version from base2

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.6.7] - 2019-05-14

### EasyRepository

- Fix namespaces in AbstractPaginatedDoctrineOrmRepository

### EasyDecision

- [#32] Improve expression language implementation

### EasyApiToken

- [#31] Create Symfony bundle

### EasyPsr7Factory

- [#30] Create Symfony bundle

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.6.6] - 2019-05-03

### EasyIdentity

- [#29] Make simple implementation easier

<!-- dumped content end -->

## [v0.6.5] - 2019-05-02

- Fix composer dependencies and wrong name in readme files

<!-- dumped content start -->

## [v0.6.4] - 2019-05-02

### EasyApiToken

- [#28] Make easy api token decoder factory extendable

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.6.3] - 2019-05-01

- [#27] Bump eoneopay/utils to 0.2

### EasyApiToken

- [#26] Add factory for EasyApiTokenDecoder objects, Thanks to [@edwardmurrell-loyaltycorp]

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.6.2] - 2019-04-26

### EasyCfhighlander

- [#25] Fix name of bin file

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.6.1] - 2019-04-26

### EasyCfhighlander

- [#24] Create package

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.6.0] - 2019-04-24

- [#22] Fix: Rename filename based on classname, Thanks to [@fsti-francohora]
- [#23] Switch to LoyaltyCorp namespaces

<!-- dumped content end -->

## [v0.4.5] - 2019-04-07

### EasyRepository

- Put getEntityClass on abstract doctrine ORM repository instead of trait

<!-- dumped content start -->

## [v0.4.4] - 2019-04-07

### EasyRepository

- [#20] Trait for DoctrineOrmRepository

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.4.3] - 2019-03-30

- [#18] Implement Coding Standards

### EasyDecision

- [#19] Add validate on ExpressionLanguage + remove addFunctionProvider

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.4.2] - 2019-03-26

### EasyDecision

- [#17] Create package

<!-- dumped content end -->

## [v0.4.1] - 2019-03-21

### EasyIdentity

- IdentityUserInterface doesn't use fluent setters to make it easier to implement

<!-- dumped content start -->

## [v0.4.0] - 2019-03-21

### EasyApiToken

- [#16] Create Auth0 JWT driver

### EasyIdentity

- Make identity service and user more generic

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.3.4] - 2019-03-15

### EasyIdentity

- Improve concept of identity user id holder

## [v0.3.3] - 2019-03-15

### EasyIdentity

- [#15] Create package

### EasyRepository

- [#14] Return an array from EloquentRepository, Thanks to [@ttam]

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.3.2] - 2019-03-12

### EasyPipeline

- [#12] Pipeline name aware, Thanks to [@ttam]

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.3.1] - 2019-03-05

- [#11] Improve Laravel service providers for DX, Thanks to [@natepage]

### EasyPipeline

- [#10] Update service provider to publish and merge config, Thanks to [@natepage]

### EasyRepository

- [#9] Update Doctrine ORM repository to define manager as entity manager an…, Thanks to [@natepage]

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.3.0] - 2019-03-02

### EasyPipeline

- [#8] Create package, Thanks to [@natepage]
- [#7] Fix typo, Thanks to [@ttam]

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.2.1] - 2019-02-26

### EasyRepository

- [#6] Add Illuminate Eloquent abstract repository, Thanks to [@natepage]

<!-- dumped content end -->

<!-- dumped content start -->

### EasyPagination

- [#4] Refactor PageEasyPaginationDataResolverTrait according to new StartSizeData, Thanks to [@AlbertLabarento]

<!-- dumped content end -->

<!-- dumped content start -->

## [v0.2.0] - 2019-02-11

### EasyRepository

- [#3] Add Doctrine ORM abstract repository with pagination, Thanks to [@natepage]

<!-- dumped content end -->

<!-- dumped content start -->

### EasyApiToken

- [#2] Add api token package, Thanks to [@natepage]

## [v0.1.1] - 2019-02-01

### EasyRepository

- [#1] Used getManagerForClass method to get correct manager, Thanks to [@maxquebral]

<!-- dumped content end -->

[#2]: https://github.com/StepTheFkUp/StepTheFkUp/pull/2
[#1]: https://github.com/StepTheFkUp/StepTheFkUp/pull/1
[@natepage]: https://github.com/natepage
[@maxquebral]: https://github.com/maxquebral
[#3]: https://github.com/StepTheFkUp/StepTheFkUp/pull/3
[#4]: https://github.com/StepTheFkUp/StepTheFkUp/pull/4
[@AlbertLabarento]: https://github.com/AlbertLabarento
[v0.2.0]: https://github.com/StepTheFkUp/StepTheFkUp/compare/v0.1.1...v0.2.0
[#6]: https://github.com/StepTheFkUp/StepTheFkUp/pull/6
[v0.2.1]: https://github.com/StepTheFkUp/StepTheFkUp/compare/v0.2.0...v0.2.1
[#8]: https://github.com/StepTheFkUp/StepTheFkUp/pull/8
[#7]: https://github.com/StepTheFkUp/StepTheFkUp/pull/7
[@ttam]: https://github.com/ttam
[#11]: https://github.com/StepTheFkUp/StepTheFkUp/pull/11
[#10]: https://github.com/StepTheFkUp/StepTheFkUp/pull/10
[#9]: https://github.com/StepTheFkUp/StepTheFkUp/pull/9
[v0.3.0]: https://github.com/StepTheFkUp/StepTheFkUp/compare/v0.2.1...v0.3.0
[#12]: https://github.com/StepTheFkUp/StepTheFkUp/pull/12
[v0.3.1]: https://github.com/StepTheFkUp/StepTheFkUp/compare/v0.3.0...v0.3.1
[#15]: https://github.com/StepTheFkUp/StepTheFkUp/pull/15
[#14]: https://github.com/StepTheFkUp/StepTheFkUp/pull/14
[v0.3.2]: https://github.com/StepTheFkUp/StepTheFkUp/compare/v0.3.1...v0.3.2
[v0.3.3]: https://github.com/StepTheFkUp/StepTheFkUp/compare/v0.3.2...v0.3.3
[#16]: https://github.com/StepTheFkUp/StepTheFkUp/pull/16
[v0.3.4]: https://github.com/StepTheFkUp/StepTheFkUp/compare/v0.3.3...v0.3.4
[#17]: https://github.com/StepTheFkUp/StepTheFkUp/pull/17
[v0.4.1]: https://github.com/StepTheFkUp/StepTheFkUp/compare/v0.4.0...v0.4.1
[v0.4.0]: https://github.com/StepTheFkUp/StepTheFkUp/compare/v0.3.4...v0.4.0
[#19]: https://github.com/StepTheFkUp/StepTheFkUp/pull/19
[#18]: https://github.com/StepTheFkUp/StepTheFkUp/pull/18
[v0.4.2]: https://github.com/StepTheFkUp/StepTheFkUp/compare/v0.4.1...v0.4.2
[#20]: https://github.com/StepTheFkUp/StepTheFkUp/pull/20
[v0.4.3]: https://github.com/StepTheFkUp/StepTheFkUp/compare/v0.4.2...v0.4.3
[#23]: https://github.com/StepTheFkUp/StepTheFkUp/pull/23
[#22]: https://github.com/StepTheFkUp/StepTheFkUp/pull/22
[@fsti-francohora]: https://github.com/fsti-francohora
[v0.4.5]: https://github.com/StepTheFkUp/StepTheFkUp/compare/v0.4.4...v0.4.5
[v0.4.4]: https://github.com/StepTheFkUp/StepTheFkUp/compare/v0.4.3...v0.4.4
[#24]: https://github.com/StepTheFkUp/StepTheFkUp/pull/24
[v0.6.0]: https://github.com/StepTheFkUp/StepTheFkUp/compare/v0.4.5...v0.6.0
[#25]: https://github.com/loyaltycorp/easy-monorepo/pull/25
[v0.6.1]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.6.0...v0.6.1
[#27]: https://github.com/loyaltycorp/easy-monorepo/pull/27
[#26]: https://github.com/loyaltycorp/easy-monorepo/pull/26
[@edwardmurrell-loyaltycorp]: https://github.com/edwardmurrell-loyaltycorp
[v0.6.2]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.6.1...v0.6.2
[#28]: https://github.com/loyaltycorp/easy-monorepo/pull/28
[v0.6.3]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.6.2...v0.6.3
[#29]: https://github.com/loyaltycorp/easy-monorepo/pull/29
[v0.6.5]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.6.4...v0.6.5
[v0.6.4]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.6.3...v0.6.4
[#32]: https://github.com/loyaltycorp/easy-monorepo/pull/32
[#31]: https://github.com/loyaltycorp/easy-monorepo/pull/31
[#30]: https://github.com/loyaltycorp/easy-monorepo/pull/30
[v0.6.6]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.6.5...v0.6.6
[#33]: https://github.com/loyaltycorp/easy-monorepo/pull/33
[v0.6.7]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.6.6...v0.6.7
[#34]: https://github.com/loyaltycorp/easy-monorepo/pull/34
[v0.6.9]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.6.8...v0.6.9
[v0.6.8]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.6.7...v0.6.8
[v0.6.12]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.6.11...v0.6.12
[v0.6.11]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.6.10...v0.6.11
[v0.6.10]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.6.9...v0.6.10
[#36]: https://github.com/loyaltycorp/easy-monorepo/pull/36
[#35]: https://github.com/loyaltycorp/easy-monorepo/pull/35
[v0.6.13]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.6.12...v0.6.13
[#39]: https://github.com/loyaltycorp/easy-monorepo/pull/39
[#38]: https://github.com/loyaltycorp/easy-monorepo/pull/38
[#37]: https://github.com/loyaltycorp/easy-monorepo/pull/37
[v0.6.14]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.6.13...v0.6.14
[#40]: https://github.com/loyaltycorp/easy-monorepo/pull/40
[v0.7.0]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.6.14...v0.7.0
[#41]: https://github.com/loyaltycorp/easy-monorepo/pull/41
[v0.7.1]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.7.0...v0.7.1
[#42]: https://github.com/loyaltycorp/easy-monorepo/pull/42
[v0.7.2]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.7.1...v0.7.2
[@bradmathews]: https://github.com/bradmathews
[#43]: https://github.com/loyaltycorp/easy-monorepo/pull/43
[v0.7.3]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.7.2...v0.7.3
[#45]: https://github.com/loyaltycorp/easy-monorepo/pull/45
[#44]: https://github.com/loyaltycorp/easy-monorepo/pull/44
[v0.7.4]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.7.3...v0.7.4
[#47]: https://github.com/loyaltycorp/easy-monorepo/pull/47
[#46]: https://github.com/loyaltycorp/easy-monorepo/pull/46
[v0.7.5]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.7.4...v0.7.5
[#48]: https://github.com/loyaltycorp/easy-monorepo/pull/48
[#50]: https://github.com/loyaltycorp/easy-monorepo/pull/50
[#49]: https://github.com/loyaltycorp/easy-monorepo/pull/49
[v0.7.6]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.7.5...v0.7.6
[#51]: https://github.com/loyaltycorp/easy-monorepo/pull/51
[v0.7.7]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.7.6...v0.7.7
[#52]: https://github.com/loyaltycorp/easy-monorepo/pull/52
[v0.7.9]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.7.8...v0.7.9
[v0.7.8]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.7.7...v0.7.8
[v0.7.10]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.7.9...v0.7.10
[#59]: https://github.com/loyaltycorp/easy-monorepo/pull/59
[#58]: https://github.com/loyaltycorp/easy-monorepo/pull/58
[#56]: https://github.com/loyaltycorp/easy-monorepo/pull/56
[#55]: https://github.com/loyaltycorp/easy-monorepo/pull/55
[#54]: https://github.com/loyaltycorp/easy-monorepo/pull/54
[#53]: https://github.com/loyaltycorp/easy-monorepo/pull/53
[@merk]: https://github.com/merk
[@albusss]: https://github.com/albusss
[v0.7.11]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.7.10...v0.7.11
[#61]: https://github.com/loyaltycorp/easy-monorepo/pull/61
[#60]: https://github.com/loyaltycorp/easy-monorepo/pull/60
[#63]: https://github.com/loyaltycorp/easy-monorepo/pull/63
[#62]: https://github.com/loyaltycorp/easy-monorepo/pull/62
[@ketanp77]: https://github.com/ketanp77
[@egor-dev]: https://github.com/egor-dev
[#64]: https://github.com/loyaltycorp/easy-monorepo/pull/64
[v0.8.0]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.7.11...v0.8.0
[#66]: https://github.com/loyaltycorp/easy-monorepo/pull/66
[#65]: https://github.com/loyaltycorp/easy-monorepo/pull/65
[v0.8.1]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.8.0...v0.8.1
[#78]: https://github.com/loyaltycorp/easy-monorepo/pull/78
[#77]: https://github.com/loyaltycorp/easy-monorepo/pull/77
[#76]: https://github.com/loyaltycorp/easy-monorepo/pull/76
[#75]: https://github.com/loyaltycorp/easy-monorepo/pull/75
[#74]: https://github.com/loyaltycorp/easy-monorepo/pull/74
[#73]: https://github.com/loyaltycorp/easy-monorepo/pull/73
[#72]: https://github.com/loyaltycorp/easy-monorepo/pull/72
[#71]: https://github.com/loyaltycorp/easy-monorepo/pull/71
[#70]: https://github.com/loyaltycorp/easy-monorepo/pull/70
[#69]: https://github.com/loyaltycorp/easy-monorepo/pull/69
[#68]: https://github.com/loyaltycorp/easy-monorepo/pull/68
[#67]: https://github.com/loyaltycorp/easy-monorepo/pull/67
[@rashmitsingh]: https://github.com/rashmitsingh
[@olamedia]: https://github.com/olamedia
[@MitchellMacpherson]: https://github.com/MitchellMacpherson
[v0.8.2]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.8.1...v0.8.2
[#81]: https://github.com/loyaltycorp/easy-monorepo/pull/81
[#80]: https://github.com/loyaltycorp/easy-monorepo/pull/80
[#79]: https://github.com/loyaltycorp/easy-monorepo/pull/79
[v0.8.3]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.8.2...v0.8.3
[#82]: https://github.com/loyaltycorp/easy-monorepo/pull/82
[#84]: https://github.com/loyaltycorp/easy-monorepo/pull/84
[#83]: https://github.com/loyaltycorp/easy-monorepo/pull/83
[v0.9.0]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.8.3...v0.9.0
[#86]: https://github.com/loyaltycorp/easy-monorepo/pull/86
[#85]: https://github.com/loyaltycorp/easy-monorepo/pull/85
[v0.9.1]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.9.0...v0.9.1
[#87]: https://github.com/loyaltycorp/easy-monorepo/pull/87
[v0.9.2]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.9.1...v0.9.2
[#89]: https://github.com/loyaltycorp/easy-monorepo/pull/89
[v0.9.3]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.9.2...v0.9.3
[#90]: https://github.com/loyaltycorp/easy-monorepo/pull/90
[@sjdaws]: https://github.com/sjdaws
[v0.9.4]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.9.3...v0.9.4
[#92]: https://github.com/loyaltycorp/easy-monorepo/pull/92
[v0.10.0]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.9.4...v0.10.0
[#93]: https://github.com/loyaltycorp/easy-monorepo/pull/93
[#91]: https://github.com/loyaltycorp/easy-monorepo/pull/91
[v0.10.2]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.10.1...v0.10.2
[v0.10.1]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.10.0...v0.10.1
[#96]: https://github.com/loyaltycorp/easy-monorepo/pull/96
[#95]: https://github.com/loyaltycorp/easy-monorepo/pull/95
[v0.10.3]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.10.2...v0.10.3
[#97]: https://github.com/loyaltycorp/easy-monorepo/pull/97
[@roman-loyaltycorp]: https://github.com/roman-loyaltycorp
[v0.10.4]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.10.3...v0.10.4
[#98]: https://github.com/loyaltycorp/easy-monorepo/pull/98
[v0.10.5]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.10.4...v0.10.5
[#99]: https://github.com/loyaltycorp/easy-monorepo/pull/99
[v0.10.6]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.10.5...v0.10.6
[#100]: https://github.com/loyaltycorp/easy-monorepo/pull/100
[v0.10.7]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.10.6...v0.10.7
[#101]: https://github.com/loyaltycorp/easy-monorepo/pull/101
[v0.10.8]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.10.7...v0.10.8
[#104]: https://github.com/loyaltycorp/easy-monorepo/pull/104
[v0.10.9]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.10.8...v0.10.9

[#106]: https://github.com/loyaltycorp/easy-monorepo/pull/106
[v0.10.10]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.10.9...v0.10.10
[#105]: https://github.com/loyaltycorp/easy-monorepo/pull/105
[v0.10.11]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.10.10...v0.10.11

[#109]: https://github.com/loyaltycorp/easy-monorepo/pull/109
[#102]: https://github.com/loyaltycorp/easy-monorepo/pull/102
[v1.0.0]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.11.0...v1.0.0
[v0.11.1]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.11.0...v0.11.1
[v0.11.0]: https://github.com/loyaltycorp/easy-monorepo/compare/v0.10.11...v0.11.0

[#110]: https://github.com/eonx-com/easy-monorepo/pull/110
[v0.11.2]: https://github.com/eonx-com/easy-monorepo/compare/v0.11.1...v0.11.2

[#111]: https://github.com/eonx-com/easy-monorepo/pull/111
[v2.0.0]: https://github.com/eonx-com/easy-monorepo/compare/v1.0.0...v2.0.0

[#112]: https://github.com/eonx-com/easy-monorepo/pull/112
[v2.0.1]: https://github.com/eonx-com/easy-monorepo/compare/v2.0.0...v2.0.1
[#115]: https://github.com/eonx-com/easy-monorepo/pull/115
[#114]: https://github.com/eonx-com/easy-monorepo/pull/114
[#113]: https://github.com/eonx-com/easy-monorepo/pull/113
[@skrut]: https://github.com/skrut
[v2.0.2]: https://github.com/eonx-com/easy-monorepo/compare/v2.0.1...v2.0.2

[#118]: https://github.com/eonx-com/easy-monorepo/pull/118
[#117]: https://github.com/eonx-com/easy-monorepo/pull/117
[#116]: https://github.com/eonx-com/easy-monorepo/pull/116
[v2.0.4]: https://github.com/eonx-com/easy-monorepo/compare/v2.0.3...v2.0.4
[v2.0.3]: https://github.com/eonx-com/easy-monorepo/compare/v2.0.2...v2.0.3

[#119]: https://github.com/eonx-com/easy-monorepo/pull/119
[#120]: https://github.com/eonx-com/easy-monorepo/pull/120
[v2.0.5]: https://github.com/eonx-com/easy-monorepo/compare/v2.0.4...v2.0.5
[#121]: https://github.com/eonx-com/easy-monorepo/pull/121
[v2.0.6]: https://github.com/eonx-com/easy-monorepo/compare/v2.0.5...v2.0.6
[#122]: https://github.com/eonx-com/easy-monorepo/pull/122
[v2.0.7]: https://github.com/eonx-com/easy-monorepo/compare/v2.0.6...v2.0.7
[#123]: https://github.com/eonx-com/easy-monorepo/pull/123
[v2.0.8]: https://github.com/eonx-com/easy-monorepo/compare/v2.0.7...v2.0.8
[#124]: https://github.com/eonx-com/easy-monorepo/pull/124
[v2.0.9]: https://github.com/eonx-com/easy-monorepo/compare/v2.0.8...v2.0.9
[#127]: https://github.com/eonx-com/easy-monorepo/pull/127
[#126]: https://github.com/eonx-com/easy-monorepo/pull/126
[#125]: https://github.com/eonx-com/easy-monorepo/pull/125
[v2.0.10]: https://github.com/eonx-com/easy-monorepo/compare/v2.0.9...v2.0.10
[#129]: https://github.com/eonx-com/easy-monorepo/pull/129
[#128]: https://github.com/eonx-com/easy-monorepo/pull/128
[v2.1.0]: https://github.com/eonx-com/easy-monorepo/compare/v2.0.10...v2.1.0
[#131]: https://github.com/eonx-com/easy-monorepo/pull/131
[#130]: https://github.com/eonx-com/easy-monorepo/pull/130
[v2.1.1]: https://github.com/eonx-com/easy-monorepo/compare/v2.1.0...v2.1.1
[#132]: https://github.com/eonx-com/easy-monorepo/pull/132
[v2.1.3]: https://github.com/eonx-com/easy-monorepo/compare/v2.1.2...v2.1.3
[v2.1.2]: https://github.com/eonx-com/easy-monorepo/compare/v2.1.1...v2.1.2
[#138]: https://github.com/eonx-com/easy-monorepo/pull/138
[#137]: https://github.com/eonx-com/easy-monorepo/pull/137
[#136]: https://github.com/eonx-com/easy-monorepo/pull/136
[#135]: https://github.com/eonx-com/easy-monorepo/pull/135
[#134]: https://github.com/eonx-com/easy-monorepo/pull/134
[#133]: https://github.com/eonx-com/easy-monorepo/pull/133
[v2.1.4]: https://github.com/eonx-com/easy-monorepo/compare/v2.1.3...v2.1.4
[#140]: https://github.com/eonx-com/easy-monorepo/pull/140
[v2.2.0]: https://github.com/eonx-com/easy-monorepo/compare/v2.1.4...v2.2.0
[#143]: https://github.com/eonx-com/easy-monorepo/pull/143
[v2.2.1]: https://github.com/eonx-com/easy-monorepo/compare/v2.2.0...v2.2.1
[#145]: https://github.com/eonx-com/easy-monorepo/pull/145
[v2.3.0]: https://github.com/eonx-com/easy-monorepo/compare/v2.2.1...v2.3.0
[#156]: https://github.com/eonx-com/easy-monorepo/pull/156
[#155]: https://github.com/eonx-com/easy-monorepo/pull/155
[#152]: https://github.com/eonx-com/easy-monorepo/pull/152
[#151]: https://github.com/eonx-com/easy-monorepo/pull/151
[#149]: https://github.com/eonx-com/easy-monorepo/pull/149
[#148]: https://github.com/eonx-com/easy-monorepo/pull/148
[#147]: https://github.com/eonx-com/easy-monorepo/pull/147
[v2.3.1]: https://github.com/eonx-com/easy-monorepo/compare/v2.3.0...v2.3.1
[@dextercampos]: https://github.com/dextercampos
[#167]: https://github.com/eonx-com/easy-monorepo/pull/167
[#166]: https://github.com/eonx-com/easy-monorepo/pull/166
[#164]: https://github.com/eonx-com/easy-monorepo/pull/164
[#163]: https://github.com/eonx-com/easy-monorepo/pull/163
[#161]: https://github.com/eonx-com/easy-monorepo/pull/161
[#160]: https://github.com/eonx-com/easy-monorepo/pull/160
[#159]: https://github.com/eonx-com/easy-monorepo/pull/159
[#158]: https://github.com/eonx-com/easy-monorepo/pull/158
[#157]: https://github.com/eonx-com/easy-monorepo/pull/157
[v2.3.2]: https://github.com/eonx-com/easy-monorepo/compare/v2.3.1...v2.3.2
[@whitesource-bolt-for-github]: https://github.com/whitesource-bolt-for-github
[#169]: https://github.com/eonx-com/easy-monorepo/pull/169
[v2.3.3]: https://github.com/eonx-com/easy-monorepo/compare/v2.3.2...v2.3.3
[#171]: https://github.com/eonx-com/easy-monorepo/pull/171
[v2.3.4]: https://github.com/eonx-com/easy-monorepo/compare/v2.3.3...v2.3.4
[#172]: https://github.com/eonx-com/easy-monorepo/pull/172
[v2.3.5]: https://github.com/eonx-com/easy-monorepo/compare/v2.3.4...v2.3.5
[#183]: https://github.com/eonx-com/easy-monorepo/pull/183
[#182]: https://github.com/eonx-com/easy-monorepo/pull/182
[#181]: https://github.com/eonx-com/easy-monorepo/pull/181
[#180]: https://github.com/eonx-com/easy-monorepo/pull/180
[#176]: https://github.com/eonx-com/easy-monorepo/pull/176
[#174]: https://github.com/eonx-com/easy-monorepo/pull/174
[#173]: https://github.com/eonx-com/easy-monorepo/pull/173
[#162]: https://github.com/eonx-com/easy-monorepo/pull/162
[v2.3.6]: https://github.com/eonx-com/easy-monorepo/compare/v2.3.5...v2.3.6
[@cainseing]: https://github.com/cainseing
[#191]: https://github.com/eonx-com/easy-monorepo/pull/191
[#189]: https://github.com/eonx-com/easy-monorepo/pull/189
[#188]: https://github.com/eonx-com/easy-monorepo/pull/188
[v2.3.7]: https://github.com/eonx-com/easy-monorepo/compare/v2.3.6...v2.3.7
