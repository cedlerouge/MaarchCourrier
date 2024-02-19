<?php

namespace MaarchCourrier\SignatureBook\Domain;

use DateTime;
use DateTimeInterface;
use JsonSerializable;

class SignedResource implements JsonSerializable
{
    private int $id = -1;
    private int $userSerialId = -1;
    private string $status = "";
    private ?DateTimeInterface $signatureDate = null;
    private ?string $encodedContent = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUserSerialId(): int
    {
        return $this->userSerialId;
    }

    public function setUserSerialId(int $userSerialId): void
    {
        $this->userSerialId = $userSerialId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getSignatureDate(): DateTimeInterface|null
    {
        return $this->signatureDate;
    }

    public function setSignatureDate(?DateTimeInterface $signatureDate): void
    {
        $this->signatureDate = $signatureDate;
    }

    public function getEncodedContent(): ?string
    {
        return $this->encodedContent;
    }

    public function setEncodedContent(?string $encodedContent): void
    {
        $this->encodedContent = $encodedContent;
    }

    public function jsonSerialize(): array
    {
        $array = [];

        if ($this->getId() > 0) {
            $array['id'] = $this->getId();
        }
        if (!empty($this->getUserSerialId())) {
            $array['userSerialId'] = $this->getUserSerialId();
        }

        if (!empty($this->getStatus())) {
            $array['status'] = $this->getStatus();
        }

        if (!empty($this->getSignatureDate())) {
            $array['signatureDate'] = $this->getSignatureDate();
        }

        if (!empty($this->getEncodedContent())) {
            $array['encodedContent'] = $this->getEncodedContent();
        }

        return $array;
    }
}
