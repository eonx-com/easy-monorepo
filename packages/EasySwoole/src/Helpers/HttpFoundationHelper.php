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

    public static function reflectHttpFoundationResponse(
        HttpFoundationResponse $hfResponse,
        Response $response,
        int $chunkSize,
        ?string $bufferedOutput = null
    ): void {
        foreach ($hfResponse->headers->all() as $name => $values) {
            $response->header((string)$name, $values);
        }

        $response->status($hfResponse->getStatusCode());

        // Support streamed responses
        if (($hfResponse instanceof BinaryFileResponse && $hfResponse->headers->has('Content-Range'))
            || $hfResponse instanceof StreamedResponse) {
            // Start output buffering, write buffer to swoole response
            \ob_start(static function ($buffer) use ($response): string {
                if (\is_string($buffer) && $buffer !== '') {
                    $response->write($buffer);
                }

                return '';
            }, 4096);

            // Send streamed content to buffer
            $hfResponse->sendContent();

            // Stop output buffering
            \ob_end_clean();

            // End swoole response
            $response->end();
        }

        // Support "simple" binary file response
        if ($hfResponse instanceof BinaryFileResponse) {
            $response->sendfile($hfResponse->getFile()->getPathname());

            return;
        }

        // From here we have a normal response, simply return the content
        // Prepend buffered output to response content (echo, var_dump, etc)
        $content = $bufferedOutput . $hfResponse->getContent();
        $length = \mb_strlen($content);

        // Do not chunk response if not needed
        if ($length <= $chunkSize) {
            $response->end($content);

            return;
        }

        // Otherwise chunk content
        for ($offset = 0; $offset < $length; $offset += $chunkSize) {
            $response->write(\substr($content, $offset, $chunkSize));
        }

        $response->end();
    }
}
