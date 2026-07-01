<?php
declare(strict_types=1);

namespace EonX\EasyServerless\SecurityHeader\Hydrator;

use Symfony\Component\HttpFoundation\Response;

final readonly class SecurityHeadersHydrator
{
    private const array HEADERS = [
        'permissions-policy' => 'accelerometer=(), camera=(), geolocation=(), gyroscope=(), magnetometer=(),'
            . ' microphone=(), payment=(), usb=()',
        'referrer-policy' => 'strict-origin-when-cross-origin',
        'strict-transport-security' => 'max-age=86400; includeSubDomains; preload',
        'x-content-type-options' => 'nosniff',
    ];

    public function hydrateResponse(Response $response): Response
    {
        foreach (self::HEADERS as $name => $value) {
            if ($response->headers->has($name)) {
                continue;
            }

            $response->headers->set($name, $value);
        }

        return $response;
    }
}
