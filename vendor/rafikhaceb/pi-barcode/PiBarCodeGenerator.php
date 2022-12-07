<?php

declare(strict_types=1);

namespace PiBarCode;

class PiBarCodeGenerator
{
    static function generate(
        string $code,
        string $type,
        int $height = 80,
        int $width = 0,
        bool $readable = false,
        bool $showType = true,
        string $foreground = '000000',
        string $bgColor = 'FFFFFF'
    ): void {
        $type = strtoupper($type);

        $barcode = new PiBarCode();
        $barcode->setSize($height, $width);

        if ($readable === false) {
            $barcode->setText('');
        }
        if ($showType === false) {
            $barcode->hideCodeType();
        }

        if ($foreground !== '') {
            if ($bgColor !== '') {
                $barcode->setColors($foreground, $bgColor);
            } else {
                $barcode->setColors($foreground);
            }
        }

        $barcode->setType($type);
        $barcode->setCode($code);

        $barcode->showBarcodeImage();
    }
}
