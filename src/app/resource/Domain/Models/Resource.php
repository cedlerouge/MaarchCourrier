<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Resource class
 * @author dev@maarch.org
 */

namespace Resource\Domain\Models;

class Resource
{
    private int     $resId;
    private ?string  $subject;
    private ?string $docserverId;
    private ?string $path;
    private ?string $filename;
    private int     $version;
    private ?string $fingerprint;
    private ?string $format;
    private int     $typist;

    public function __construct(
        int $resId,
        ?string $subject,
        ?string $docserverId,
        ?string $path,
        ?string $filename,
        int $version,
        ?string $fingerprint,
        ?string $format,
        int $typist
    ) {
        $this->resId = $resId;
        $this->subject = $subject;
        $this->docserverId = $docserverId;
        $this->path = $path;
        $this->filename = $filename;
        $this->version = $version;
        $this->fingerprint = $fingerprint;
        $this->format = $format;
        $this->typist = $typist;
    }

    public function getResId(): int
    {
        return $this->resId;
    }

    public function setResId(int $resId): void
    {
        $this->resId = $resId;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): void
    {
        $this->subject = $subject;
    }

    public function getDocserverId(): ?string
    {
        return $this->docserverId;
    }

    public function setDocserverId(?string $docserverId): void
    {
        $this->docserverId = $docserverId;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): void
    {
        $this->path = $path;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename): void
    {
        $this->filename = $filename;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setVersion(int $version): void
    {
        $this->version = $version;
    }

    public function getFingerprint(): ?string
    {
        return $this->fingerprint;
    }

    public function setFingerprint(?string $fingerprint): void
    {
        $this->fingerprint = $fingerprint;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function setFormat(?string $format): void
    {
        $this->format = $format;
    }

    public function getTypist(): int
    {
        return $this->typist;
    }

    public function setTypist(int $typist): void
    {
        $this->typist = $typist;
    }
}
