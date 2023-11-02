<?php

declare(strict_types=1);

namespace MaarchCourrier\Tests\app\external\Pastell\Mock;

use ExternalSignatoryBook\pastell\Application\SendToPastell;

class SendToPastellSpy extends SendToPastell
{
    public array $annexes = [];

    public function sendFolderToPastell(int $resId, string $title, string $sousType, string $filePath, array $annexes = []): array
    {
        $this->annexes = $annexes;

        return parent::sendFolderToPastell($resId, $title, $sousType, $filePath, $annexes);
    }

}
