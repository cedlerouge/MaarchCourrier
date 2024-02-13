<?php

namespace MaarchCourrier\SignatureBook\Domain;

use DateTime;
use JsonSerializable;

class SignedResource implements JsonSerializable
{
    private int $id;
    private int $userSerialId;
    private string $status;
    private DateTime $signatureDate;

    public function jsonSerialize(): mixed
    {
        // TODO: Implement jsonSerialize() method.
    }
}
