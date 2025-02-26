<?php

namespace App\Property\DTOs;

class PropertyDTO
{
    public function __construct(
        public int $id,
        public string $platformSlug,
        public string $name,
        public string $url,
        public ?array $extra = []
    ) {
    }
}
