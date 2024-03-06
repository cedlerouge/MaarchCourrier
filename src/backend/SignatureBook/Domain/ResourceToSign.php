<?php

namespace MaarchCourrier\SignatureBook\Domain;

use JsonSerializable;

class ResourceToSign implements JsonSerializable
{
    private int $resId;
    private string $title;
    private string $chrono;
    private ?int $signedResId;
    private int $resType;

    /**
     * @return int
     */
    public function getResId(): int
    {
        return $this->resId;
    }

    /**
     * @param int $resId
     *
     * @return ResourceToSign
     */
    public function setResId(int $resId): self
    {
        $this->resId = $resId;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return ResourceToSign
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getChrono(): string
    {
        return $this->chrono;
    }

    /**
     * @param string $chrono
     *
     * @return ResourceToSign
     */
    public function setChrono(string $chrono): self
    {
        $this->chrono = $chrono;
        return $this;
    }

    /**
     * @return ?int
     */
    public function getSignedResId(): ?int
    {
        return $this->signedResId;
    }

    /**
     * @param ?int $signedResId
     *
     * @return ResourceToSign
     */
    public function setSignedResId(?int $signedResId): self
    {
        $this->signedResId = $signedResId;
        return $this;
    }

    /**
     * @return int
     */
    public function getResType(): int
    {
        return $this->resType;
    }

    /**
     * @param int $resType
     *
     * @return ResourceToSign
     */
    public function setResType(int $resType): self
    {
        $this->resType = $resType;
        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'resId' => $this->getResId(),
            'title' => $this->getTitle(),
            'chrono' => $this->getChrono(),
            'signedResId' => $this->getSignedResId(),
            'resType' => $this->getResType()
        ];
    }
}
