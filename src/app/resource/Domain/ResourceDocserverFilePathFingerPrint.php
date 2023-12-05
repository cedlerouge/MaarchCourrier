<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Resource file info class
 * @author dev@maarch.org
 */

namespace Resource\Domain;

class ResourceDocserverFilePathFingerPrint
{
    private Docserver   $docserver;
    private string      $filePath;
    private string      $fingerprint;

    /**
     * @param Docserver $docserver
     * @param string $filePath
     * @param string $fingerprint
     */
    public function __construct(Docserver $docserver, string $filePath, string $fingerprint)
    {
        $this->docserver = $docserver;
        $this->filePath = $filePath;
        $this->fingerprint = $fingerprint;
    }

    public function getDocserver(): Docserver
    {
        return $this->docserver;
    }

    public function setDocserver(Docserver $docserver): void
    {
        $this->docserver = $docserver;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): void
    {
        $this->filePath = $filePath;
    }

    public function getFingerprint(): string
    {
        return $this->fingerprint;
    }

    public function setFingerprint(string $fingerprint): void
    {
        $this->fingerprint = $fingerprint;
    }
}

