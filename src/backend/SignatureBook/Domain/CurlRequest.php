<?php

namespace MaarchCourrier\SignatureBook\Domain;

use JsonSerializable;

class CurlRequest implements JsonSerializable
{
    private string $url = "";
    private string $method = "";
    private array $header = [];
    private array $body = [];
    private ?int $httpCode = null;
    private ?array $contentReturn = null;

    public function createFromArray(array $array = []): CurlRequest
    {
        $request = new CurlRequest();

        $request->setUrl($array['url']);
        $request->setMethod($array['method']);
        (!empty($array['header'])) ? $request->setHeader($array['header']) : $request->setHeader([]);
        (!empty($array['body'])) ? $request->setBody($array['body']) : $request->setBody([]);

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

    public function getContentReturn(): ?array
    {
        return $this->contentReturn;
    }

    public function setContentReturn(?array $contentReturn): void
    {
        $this->contentReturn = $contentReturn;
    }

    public function getHeader(): array
    {
        return $this->header;
    }

    public function setHeader(array $header): void
    {
        $this->header = $header;
    }

    public function jsonSerialize(): array
    {
        return [
            'url'      => $this->getUrl(),
            'method'   => $this->getMethod(),
            'body'     => $this->getBody(),
            'httpCode' => $this->getHttpCode(),
            'content'  => $this->getContentReturn()
        ];
    }
}
