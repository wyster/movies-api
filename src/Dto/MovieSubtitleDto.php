<?php

declare(strict_types=1);

namespace App\Dto;

use OpenApi\Attributes as OA;

class MovieSubtitleDto
{
    public function __construct(
        #[OA\Property(type: 'string', example: 'English')]
        public readonly string $lang,
        #[OA\Property(type: 'string', format: 'uri', example: 'https://example.com/subtitles.vtt')]
        public readonly string $url,
    ) {}
}
