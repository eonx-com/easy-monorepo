<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge\Laravel;

use EonX\EasyRequestId\Bridge\BridgeConstantsInterface;
use EonX\EasyRequestId\DefaultResolver;
use EonX\EasyRequestId\Interfaces\FallbackResolverInterface;
use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;
use EonX\EasyRequestId\RequestIdService;
use EonX\EasyRequestId\UuidV4FallbackResolver;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

final class EasyRequestIdServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/easy-request-id.php' => \base_path('config/easy-request-id.php'),
        ]);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/easy-request-id.php', 'easy-request-id');

        $this->app->singleton(FallbackResolverInterface::class, UuidV4FallbackResolver::class);

        $this->app->singleton(RequestIdServiceInterface::class, function (): RequestIdServiceInterface {
            return new RequestIdService(
                $this->app->make(Request::class),
                $this->app->tagged(BridgeConstantsInterface::TAG_REQUEST_ID_RESOLVER),
                $this->app->tagged(BridgeConstantsInterface::TAG_CORRELATION_ID_RESOLVER),
                $this->app->make(FallbackResolverInterface::class)
            );
        });

        if ((bool)\config('easy-request-id.default_resolver', true)) {
            $this->app->singleton(DefaultResolver::class, function (): DefaultResolver {
                return new DefaultResolver(
                    \config('easy-request-id.default_request_id_header'),
                    \config('easy-request-id.default_correlation_id_header')
                );
            });
        }
    }
}
