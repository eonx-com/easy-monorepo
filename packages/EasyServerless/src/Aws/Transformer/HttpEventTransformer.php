<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Aws\Transformer;

final class HttpEventTransformer implements HttpEventTransformerInterface
{
    public function transform(array $event): array
    {
        // We have experienced issues with multiple cookies while playing with custom domain setup, resulting in
        // cookies being set as multiple items in the array instead of a single string separated by `; ` which
        // resulted in Bref ignoring them, this normalizes them back to a single string if needed
        if (\is_array($event['multiValueHeaders']['cookie'] ?? null)
            && \count($event['multiValueHeaders']['cookie']) > 1) {
            $cookies = [];

            foreach ($event['multiValueHeaders']['cookie'] as $index => $cookie) {
                if (\is_int($index) && \is_string($cookie) && \str_contains($cookie, '=')) {
                    $cookies[] = $cookie;
                }
            }

            $event['multiValueHeaders']['cookie'] = [\implode('; ', $cookies)];
        }

        return $event;
    }
}
