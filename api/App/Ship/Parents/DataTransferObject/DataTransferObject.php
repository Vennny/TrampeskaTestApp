<?php

declare(strict_types=1);

namespace App\Ship\Parents\DataTransferObject;

abstract class DataTransferObject
{
    /**
     * @var mixed[]
     */
    protected array $attributes = [];

    /**
     * @return mixed[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
