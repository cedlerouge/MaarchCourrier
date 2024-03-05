<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief   CurlService mock
 * @author  dev@maarch.org
 */

namespace MaarchCourrier\Tests\Unit\SignatureBook\Mock\Webhook;

use MaarchCourrier\Core\Domain\Curl\CurlRequest;
use MaarchCourrier\Core\Domain\Curl\CurlResponse;
use MaarchCourrier\Core\Domain\Port\CurlServiceInterface;

class CurlServiceMock implements CurlServiceInterface
{
    public int $httpCode = 200;
    public bool $badRequest = false;

    public function call(CurlRequest $curlRequest): CurlRequest
    {
        if ($this->badRequest) {
            $returnFromParapheur = [
                'errors' => "Error from parapheur"
            ];
        } else {
            $returnFromParapheur = [
                'encodedDocument' => base64_encode(file_get_contents("install/samples/attachments/2021/03/0001/0003_1072724674.pdf")),
                'mimetype' => "application/pdf",
                'filename' => "PDF_signature.pdf"
            ];
        }

        $curlResponse = new CurlResponse($this->httpCode, $returnFromParapheur);
        $curlRequest->setCurlResponse($curlResponse);

        return $curlRequest;
    }
}
