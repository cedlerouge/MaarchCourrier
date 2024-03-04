<?php

namespace MaarchCourrier\SignatureBook\Domain;

use JsonSerializable;

class ResourcesToSign implements JsonSerializable
{
    private int $resId;
    private string $title;
    private string $chrono;
    private int $signedResId;
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
     */
    public function setResId(int $resId): void
    {
        $this->resId = $resId;
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
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
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
     */
    public function setChrono(string $chrono): void
    {
        $this->chrono = $chrono;
    }

    /**
     * @return int
     */
    public function getSignedResId(): int
    {
        return $this->signedResId;
    }

    /**
     * @param int $signedResId
     */
    public function setSignedResId(int $signedResId): void
    {
        $this->signedResId = $signedResId;
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
     */
    public function setResType(int $resType): void
    {
        $this->resType = $resType;
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
