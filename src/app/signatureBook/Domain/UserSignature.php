<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief   User Signature
* @author  dev@maarch.org
*/

namespace SignatureBook\Domain;

use JsonSerializable;

class UserSignature implements JsonSerializable
{
    private int $id;
    private int $userSerialId;
    private string $signatureLabel;
    private string $signaturePath;
    private string $signatureFileName;
    private string $fingerprint;

    /**
     * @param array $array
     * @return UserSignature
     */
    public static function createFromArray(array $array = []): UserSignature
    {
        $userSignature = new UserSignature();

        $userSignature->setId($array['id'] ?? 0);
        $userSignature->setUserSerialId($array['user_serial_id'] ?? 0);
        $userSignature->setSignatureLabel($array['signature_label'] ?? '');
        $userSignature->setSignaturePath($array['signature_path'] ?? '');
        $userSignature->setSignatureFileName($array['signature_file_name'] ?? '');
        $userSignature->setFingerprint($array['fingerprint'] ?? '');

        return $userSignature;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getUserSerialId(): int
    {
        return $this->userSerialId;
    }

    /**
     * @param int $userSerialId
     */
    public function setUserSerialId(int $userSerialId): void
    {
        $this->userSerialId = $userSerialId;
    }

    /**
     * @return string
     */
    public function getSignatureLabel(): string
    {
        return $this->signatureLabel;
    }

    /**
     * @param string $signatureLabel
     */
    public function setSignatureLabel(string $signatureLabel): void
    {
        $this->signatureLabel = $signatureLabel;
    }

    /**
     * @return string
     */
    public function getSignaturePath(): string
    {
        return $this->signaturePath;
    }

    /**
     * @param string $signaturePath
     */
    public function setSignaturePath(string $signaturePath): void
    {
        $this->signaturePath = $signaturePath;
    }

    /**
     * @return string
     */
    public function getSignatureFileName(): string
    {
        return $this->signatureFileName;
    }

    /**
     * @param string $signatureFileName
     */
    public function setSignatureFileName(string $signatureFileName): void
    {
        $this->signatureFileName = $signatureFileName;
    }

    /**
     * @return string
     */
    public function getFingerprint(): string
    {
        return $this->fingerprint;
    }

    /**
     * @param string $fingerprint
     */
    public function setFingerprint(string $fingerprint): void
    {
        $this->fingerprint = $fingerprint;
    }


    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'userSerialId' => $this->getUserSerialId(),
            'signatureLabel' => $this->getSignatureLabel(),
            'signaturePath' => $this->getSignaturePath(),
            'signatureFileName' => $this->getSignatureFileName(),
            'fingerprint' => $this->getFingerprint(),
        ];
    }
}
