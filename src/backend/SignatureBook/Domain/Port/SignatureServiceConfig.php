<?php

namespace MaarchCourrier\SignatureBook\Domain\Port;

class SignatureServiceConfig
{
    private string $url;

    /**
     * @param string $url
     */
    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): SignatureServiceConfig
    {
        $this->url = $url;
        return $this;
    }

}
