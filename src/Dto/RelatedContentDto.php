<?php

declare(strict_types=1);

namespace App\Dto;

final class RelatedContentDto
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly ?string $year,
    ) {}
}
