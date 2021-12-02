<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Laravel\ApiFormats\Responses;

use EonX\EasyCore\Bridge\Laravel\ApiFormats\Interfaces\FormattedApiResponseInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class FormattedApiResponse extends Response implements FormattedApiResponseInterface
{
    /**
     * @param mixed $content
     * @param null|mixed[] $headers
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($content, ?int $statusCode = null, ?array $headers = null)
    {
        parent::__construct();

        $this->content = $content;
        $this->statusCode = $statusCode ?? 200;
        $this->headers = new ResponseHeaderBag($headers ?? []);
    }

    /**
     * @return string[]
     */
    public function getHeaders(): array
    {
        /** @var string[] $headers */
        $headers = $this->headers->all();

        return $headers;
    }
}
