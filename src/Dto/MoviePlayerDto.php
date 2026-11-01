<?php

declare(strict_types=1);

namespace App\Dto;

use OpenApi\Attributes as OA;

class MoviePlayerDto
{
    /**
     * @param array<int, MovieStreamDto> $streams
     * @param array<int, MovieSubtitleDto> $subtitles
     */
    public function __construct(
        public readonly array $streams,
        public readonly array $subtitles
    ) {}
}
