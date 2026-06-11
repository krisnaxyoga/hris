<?php

namespace App\Enums;

enum WorkArrangement: string
{
    case Office = 'office';
    case Hybrid = 'hybrid';
    case Remote = 'remote';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
