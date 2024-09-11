<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Builder;

use EonX\EasyErrorHandler\Common\Translator\TranslatorInterface;
use EonX\EasyUtils\Common\Enum\HttpStatusCode;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

final class HttpExceptionErrorResponseBuilder extends AbstractErrorResponseBuilder
{
    private const ERROR_HTTP_NOT_FOUND_MESSAGE = 'exceptions.not_found';

    private const KEY_MESSAGE = 'message';

    private readonly array $keys;

    public function __construct(
        private TranslatorInterface $translator,
        ?array $keys = null,
        ?int $priority = null,
    ) {
        $this->keys = $keys ?? [];

        parent::__construct($priority);
    }

    public function buildData(Throwable $throwable, array $data): array
    {
        if ($throwable instanceof HttpExceptionInterface) {
            $key = $this->keys[self::KEY_MESSAGE] ?? self::KEY_MESSAGE;
            $message = $throwable->getMessage();

            if ($throwable instanceof NotFoundHttpException) {
                $message = $this->translator->trans(self::ERROR_HTTP_NOT_FOUND_MESSAGE, []);
            }

            $data[$key] = $message;
        }

        return parent::buildData($throwable, $data);
    }

    public function buildStatusCode(Throwable $throwable, ?HttpStatusCode $statusCode = null): ?HttpStatusCode
    {
        if ($throwable instanceof HttpExceptionInterface) {
            $statusCode = HttpStatusCode::from($throwable->getStatusCode());
        }

        return parent::buildStatusCode($throwable, $statusCode);
    }
}
