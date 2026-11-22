<?php

declare(strict_types=1);

namespace App\Dto;

final class TmdbMovieDetailsDto
{
    /** @param list<string> $genres */
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly string $originalTitle,
        public readonly ?string $overview,
        public readonly ?string $releaseDate,
        public readonly ?string $posterPath,
        public readonly ?string $backdropPath,
        public readonly float $voteAverage,
        public readonly array $genres,
    ) {}
}
