<?php

namespace MaarchCourrier\SignatureBook\Domain;

use JsonSerializable;

class CurlResponse implements JsonSerializable
{
    public function __construct(private int $httpCode, private array $contentReturn)
    {
    }

    public function getHttpCode(): int
    {
        return $this->httpCode;
    }

    public function setHttpCode(int $httpCode): void
    {
        $this->httpCode = $httpCode;
    }

    public function getContentReturn(): array
    {
        return $this->contentReturn;
    }

    public function setContentReturn(array $contentReturn): void
    {
        $this->contentReturn = $contentReturn;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'httpCode'      => $this->httpCode,
            'contentReturn' => $this->contentReturn
        ];
    }
}
