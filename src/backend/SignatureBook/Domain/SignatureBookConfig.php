<?php

namespace MaarchCourrier\SignatureBook\Domain;

use JsonSerializable;

class SignatureBookConfig implements JsonSerializable
{
    private bool $isNewInternalParaph;
    private string $url;


    public function __construct()
    {
        $this->isNewInternalParaph = false;
        $this->url = '';
    }

    /**
     * @param array $array
     *
     * @return SignatureBookConfig
     */
    public static function createFromArray(array $array = []): SignatureBookConfig
    {
        $signatureBookConfig = new SignatureBookConfig();

        $signatureBookConfig
            ->setIsNewInternalParaph($array['isNewInternalParaph'] ?? false)
            ->setUrl($array['url'] ?? '');

        return $signatureBookConfig;
    }

    /**
     * @return bool
     */
    public function isNewInternalParaph(): bool
    {
        return $this->isNewInternalParaph;
    }

    /**
     * @param bool $isNewInternalParaph
     *
     * @return SignatureBookConfig
     */
    public function setIsNewInternalParaph(bool $isNewInternalParaph): self
    {
        $this->isNewInternalParaph = $isNewInternalParaph;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return SignatureBookConfig
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function jsonSerialize(): array
    {
        $array = [];

        $array['isNewInternalParaph'] = $this->isNewInternalParaph();

        if (!empty($this->getUrl())) {
            $array['url'] = $this->getUrl();
        }

        return $array;
    }
}
