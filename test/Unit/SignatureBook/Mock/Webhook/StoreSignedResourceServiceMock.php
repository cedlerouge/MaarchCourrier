<?php

namespace MaarchCourrier\Tests\Unit\SignatureBook\Mock\Webhook;

use MaarchCourrier\SignatureBook\Domain\Port\StoreSignedResourceServiceInterface;
use MaarchCourrier\SignatureBook\Domain\SignedResource;

class StoreSignedResourceServiceMock implements StoreSignedResourceServiceInterface
{
    private bool $errorStorage = false;
    private int $resIdNewSignedDoc = 1;

    public function storeResource(SignedResource $signedResource): array
    {
        if ($this->errorStorage) {
            return ['errors' => '[storeRessourceOnDocserver] Error during storing signed response'];
        }

        $path_template = 'install/samples/resources/';
        $destinationDir = $path_template . '2023/11/0001/';
        $directory = substr($destinationDir, strlen($path_template));

        return [
            'path_template'         => $path_template,
            'destination_dir'       => $directory,
            'directory'             => $directory,
            'docserver_id'          => 'FASTHD_MAN',
            'file_destination_name' => 'toto.pdf',
            'fileSize'              => 56899,
            'fingerPrint'           => "file fingerprint"
        ];
    }

    public function storeAttachement(SignedResource $signedResource, array $attachment): int|array
    {
        if ($this->errorStorage) {
            return ['errors' => 'Error on attachment storage'];
        }
        return $this->resIdNewSignedDoc;
    }
}
