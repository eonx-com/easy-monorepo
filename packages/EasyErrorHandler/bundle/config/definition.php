<?php
declare(strict_types=1);

use EonX\EasyUtils\Common\Enum\HttpStatusCode;
use Monolog\Level;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->arrayNode('bugsnag')
                ->canBeDisabled()
                ->children()
                    ->enumNode('threshold')
                        ->values(Level::cases())
                        ->defaultNull()
                    ->end()
                    ->arrayNode('handled_exceptions')
                        ->beforeNormalization()->castToArray()->end()
                        ->scalarPrototype()->end()
                    ->end()
                    ->arrayNode('ignored_exceptions')
                        ->defaultValue([
                            HttpExceptionInterface::class,
                            RequestExceptionInterface::class,
                        ])
                        ->beforeNormalization()->castToArray()->end()
                        ->scalarPrototype()->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('logger')
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('exception_log_levels')
                        ->useAttributeAsKey('class')
                        ->beforeNormalization()->castToArray()->end()
                        ->integerPrototype()->end()
                        ->defaultValue([
                            HttpExceptionInterface::class => Level::Debug->value,
                            RequestExceptionInterface::class => Level::Debug->value,
                        ])
                    ->end()
                    ->arrayNode('ignored_exceptions')
                        ->beforeNormalization()->castToArray()->end()
                        ->scalarPrototype()->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('ignored_exceptions')
                ->beforeNormalization()->castToArray()->end()
                ->scalarPrototype()->end()
            ->end()
            ->booleanNode('report_retryable_exception_attempts')->defaultFalse()->end()
            ->booleanNode('skip_reported_exceptions')->defaultFalse()->end()
            ->booleanNode('verbose')->defaultFalse()->end()
            ->booleanNode('use_default_builders')->defaultTrue()->end()
            ->booleanNode('use_default_reporters')->defaultTrue()->end()
            ->scalarNode('translation_domain')->defaultValue('messages')->end()
            ->arrayNode('response')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('code')->defaultValue('code')->end()
                    ->scalarNode('exception')->defaultValue('exception')->end()
                    ->arrayNode('extended_exception_keys')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('class')->defaultValue('class')->end()
                            ->scalarNode('file')->defaultValue('file')->end()
                            ->scalarNode('line')->defaultValue('line')->end()
                            ->scalarNode('message')->defaultValue('message')->end()
                            ->scalarNode('trace')->defaultValue('trace')->end()
                        ->end()
                    ->end()
                    ->scalarNode('message')->defaultValue('message')->end()
                    ->scalarNode('sub_code')->defaultValue('sub_code')->end()
                    ->scalarNode('time')->defaultValue('time')->end()
                    ->scalarNode('violations')->defaultValue('violations')->end()
                ->end()
            ->end()
            ->arrayNode('translate_internal_error_messages')
                ->canBeEnabled()
                ->children()
                    ->scalarNode('locale')->defaultValue('en')->end()
                ->end()
            ->end()
            ->scalarNode('error_codes_interface')->defaultNull()->end()
            ->scalarNode('error_codes_category_size')->defaultValue(100)->end()
            ->arrayNode('exception_messages')
                ->useAttributeAsKey('class')
                ->scalarPrototype()->end()
                ->defaultValue([
                    AccessDeniedHttpException::class => 'exceptions.forbidden',
                    BadRequestHttpException::class => 'exceptions.bad_request',
                    ConflictHttpException::class => 'exceptions.conflict',
                    NotFoundHttpException::class => 'exceptions.not_found',
                    UnauthorizedHttpException::class => 'exceptions.unauthorized',
                ])
            ->end()
            ->arrayNode('exception_to_status_code')
                ->useAttributeAsKey('class')
                ->enumPrototype()
                    ->values(HttpStatusCode::cases())
                ->end()
                ->defaultValue([])
            ->end()
        ->end();
};
