<?php

namespace MaarchCourrier\SignatureBook\Domain;

use JsonSerializable;

class SignatureBookResource implements JsonSerializable
{
    private int $resId;
    private string $title;
    private string $chrono;
    private ?int $signedResId;
    private int $resType;
    private string $type;
    private bool $isConverted;

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
     * @return SignatureBookResource
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
     * @return SignatureBookResource
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
     * @return SignatureBookResource
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
     * @return SignatureBookResource
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
     * @return SignatureBookResource
     */
    public function setResType(int $resType): self
    {
        $this->resType = $resType;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return SignatureBookResource
     */
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return bool
     */
    public function isConverted(): bool
    {
        return $this->isConverted;
    }

    /**
     * @param bool $isConverted
     *
     * @return SignatureBookResource
     */
    public function setIsConverted(bool $isConverted): self
    {
        $this->isConverted = $isConverted;
        return $this;
    }



    public function jsonSerialize(): array
    {
        return [
            'resId' => $this->getResId(),
            'title' => $this->getTitle(),
            'chrono' => $this->getChrono(),
            'signedResId' => $this->getSignedResId(),
            'resType' => $this->getResType(),
            'type' => $this->getType(),
            'isConverted' => $this->isConverted()
        ];
    }
}
