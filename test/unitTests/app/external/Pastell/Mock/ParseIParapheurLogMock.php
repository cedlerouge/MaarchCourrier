<?php

declare(strict_types=1);

namespace MaarchCourrier\Tests\app\external\Pastell\Mock;

use ExternalSignatoryBook\pastell\Application\ParseIParapheurLog;

class ParseIParapheurLogMock extends ParseIParapheurLog
{
    public function parseLogIparapheur(int $resId, string $idFolder): array
    {
        if ($resId === 42) {
            return [
                'status'      => 'validated',
                'format'      => 'pdf',
                'encodedFile' => 'toto'
            ];
        }

        return [
            'status' => 'waiting'
        ];
    }

}
