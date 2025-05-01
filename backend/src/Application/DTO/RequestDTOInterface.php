<?php

namespace App\Application\DTO;

interface RequestDTOInterface
{
    public static function createFromRequestData(array $data): self;
}