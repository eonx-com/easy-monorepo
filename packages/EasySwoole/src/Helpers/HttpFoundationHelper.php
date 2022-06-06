<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Helpers;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class HttpFoundationHelper
{
    public static function fromSwooleRequest(Request $request): HttpFoundationRequest
    {
        $content = $request->rawContent();

        $hfRequest = new HttpFoundationRequest(
            $request->get ?? [],
            $request->post ?? [],
            [],
            $request->cookie ?? [],
            $request->files ?? [],
            \array_change_key_case($request->server ?? [], \CASE_UPPER),
            \is_string($content) ? $content : null
        );

        $hfRequest->headers = new HeaderBag($request->header ?? []);

        return $hfRequest;
    }

    public static function reflectHttpFoundationResponse(HttpFoundationResponse $hfResponse, Response $response): void
    {
        foreach ($hfResponse->headers->all() as $name => $values) {
            $response->header((string) $name, $values);
        }

        $response->status($hfResponse->getStatusCode());

        switch (true) {
            case $hfResponse instanceof BinaryFileResponse && $hfResponse->headers->has('Content-Range'):
            case $hfResponse instanceof StreamedResponse:
                \ob_start(static function ($buffer) use ($response) {
                    $response->write($buffer);

                    return '';
                }, 4096);
                $hfResponse->sendContent();
                \ob_end_clean();
                $response->end();
                break;
            case $hfResponse instanceof BinaryFileResponse:
                $response->sendfile($hfResponse->getFile()->getPathname());
                break;
            default:
                $response->end($hfResponse->getContent());
        }
    }
}
