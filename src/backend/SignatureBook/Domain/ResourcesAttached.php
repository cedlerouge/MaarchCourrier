<?php

namespace MaarchCourrier\SignatureBook\Domain;

use JsonSerializable;

class ResourcesAttached implements JsonSerializable
{
    private int $resId;
    private int $title;
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
     * @return int
     */
    public function getTitle(): int
    {
        return $this->title;
    }

    /**
     * @param int $title
     */
    public function setTitle(int $title): void
    {
        $this->title = $title;
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
            'signedResId' => $this->getSignedResId(),
            'resType' => $this->getResType()
        ];
    }
}
