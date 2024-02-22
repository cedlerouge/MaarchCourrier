<?php

namespace MaarchCourrier\Tests\Unit\SignatureBook\Mock\Webhook;

use MaarchCourrier\SignatureBook\Domain\CurlRequest;
use MaarchCourrier\SignatureBook\Domain\Port\CurlServiceInterface;

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

        $curlRequest->setHttpCode($this->httpCode);
        $curlRequest->setContentReturn($returnFromParapheur);

        return $curlRequest;
    }
}
