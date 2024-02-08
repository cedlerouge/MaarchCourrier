<?php

namespace MaarchCourrier\Core\Domain\Port;

interface EnvironnementInterface
{
    public function isDebug(): bool;
}
