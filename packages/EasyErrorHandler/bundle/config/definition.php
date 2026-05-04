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
                        ->stringPrototype()->end()
                    ->end()
                    ->arrayNode('ignored_exceptions')
                        ->defaultValue([
                            HttpExceptionInterface::class,
                            RequestExceptionInterface::class,
                        ])
                        ->beforeNormalization()->castToArray()->end()
                        ->stringPrototype()->end()
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
                        ->stringPrototype()->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('ignored_exceptions')
                ->beforeNormalization()->castToArray()->end()
                ->stringPrototype()->end()
            ->end()
            ->booleanNode('report_retryable_exception_attempts')->defaultFalse()->end()
            ->booleanNode('skip_reported_exceptions')->defaultFalse()->end()
            ->booleanNode('verbose')->defaultFalse()->end()
            ->booleanNode('use_default_builders')->defaultTrue()->end()
            ->booleanNode('use_default_reporters')->defaultTrue()->end()
            ->stringNode('translation_domain')->defaultValue('messages')->end()
            ->arrayNode('response')
                ->addDefaultsIfNotSet()
                ->children()
                    ->stringNode('code')->defaultValue('code')->end()
                    ->stringNode('exception')->defaultValue('exception')->end()
                    ->arrayNode('extended_exception_keys')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->stringNode('class')->defaultValue('class')->end()
                            ->stringNode('file')->defaultValue('file')->end()
                            ->stringNode('line')->defaultValue('line')->end()
                            ->stringNode('message')->defaultValue('message')->end()
                            ->stringNode('trace')->defaultValue('trace')->end()
                        ->end()
                    ->end()
                    ->stringNode('message')->defaultValue('message')->end()
                    ->stringNode('sub_code')->defaultValue('sub_code')->end()
                    ->stringNode('time')->defaultValue('time')->end()
                    ->stringNode('violations')->defaultValue('violations')->end()
                ->end()
            ->end()
            ->arrayNode('translate_internal_error_messages')
                ->canBeEnabled()
                ->children()
                    ->stringNode('locale')->defaultValue('en')->end()
                ->end()
            ->end()
            ->stringNode('error_codes_interface')->defaultNull()->end()
            ->integerNode('error_codes_category_size')->defaultValue(100)->end()
            ->arrayNode('exception_to_message')
                ->useAttributeAsKey('class')
                ->stringPrototype()->end()
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
            ->arrayNode('exception_to_code')
                ->useAttributeAsKey('class')
                ->variablePrototype()
                    ->validate()
                        ->ifTrue(
                            static fn ($value): bool => \is_int($value) === false
                                && \is_string($value) === false
                                && ($value instanceof BackedEnum) === false
                        )
                        ->thenInvalid('The validation_error_code must be an int, string, or BackedEnum.')
                    ->end()
                ->end()
                ->defaultValue([])
            ->end()
        ->end();
};
