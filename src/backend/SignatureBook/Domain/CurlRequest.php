<?php

namespace MaarchCourrier\SignatureBook\Domain;

class CurlRequest
{
    private string $url;
    private string $method;
    private array $body;
    private ?int $httpCode;
    private ?string $contentReturn;

    public static function createFromArray(array $array = []): CurlRequest
    {
        $request = new CurlRequest();

        $request->setUrl($array['url']);
        $request->setMethod($array['method']);
        $request->setBody($array['body']);

        return $request;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setMethod(string $method): void
    {
        $this->method = $method;
    }

    public function getBody(): array
    {
        return $this->body;
    }

    public function setBody(array $body): void
    {
        $this->body = $body;
    }

    public function getHttpCode(): ?int
    {
        return $this->httpCode;
    }

    public function setHttpCode(?int $httpCode): void
    {
        $this->httpCode = $httpCode;
    }

    public function getContentReturn(): ?string
    {
        return $this->contentReturn;
    }

    public function setContentReturn(?string $contentReturn): void
    {
        $this->contentReturn = $contentReturn;
    }


}
