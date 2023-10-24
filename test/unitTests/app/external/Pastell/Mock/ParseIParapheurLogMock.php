<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

declare(strict_types=1);

namespace MaarchCourrier\Tests\app\external\Pastell\Mock;

use ExternalSignatoryBook\pastell\Application\ParseIParapheurLog;

class ParseIParapheurLogMock extends ParseIParapheurLog
{
    /**
     * @param int $resId
     * @param string $idFolder
     * @return string[]
     */
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
