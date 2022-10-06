<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Logs Processor
* @author dev@maarch.org
* @ingroup core
*/

namespace SrcCore\processors;

use SrcCore\models\TextFormatModel;
use SrcCore\models\ValidatorModel;
use SrcCore\models\CoreConfigModel;

// using Monolog version 2.6.0

class LogProcessor
{
    private $extraData;

    public function __construct($extraData)
    {
        $this->extraData = $extraData;
    }

    public function __invoke(array $record)
    {
        $record['extra']['processId'] = getmypid();
        $record['extra']['extraData'] = $this->extraData;

        return $record;
    }
}