<?php

declare(strict_types=1);

namespace App\Dto;

use OpenApi\Attributes as OA;

class MovieStreamDto
{
    public function __construct(
        #[OA\Property(type: 'string', example: '360p')]
        public readonly string $quality,
        #[OA\Property(type: 'string', format: 'uri', example: 'https://example.com/manifest.m3u8')]
        public readonly string $playlist,
        #[OA\Property(type: 'string', format: 'uri', example: 'https://example.com/video.mp4')]
        public readonly string $video,
    ) {}
}
