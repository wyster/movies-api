<?php

declare(strict_types=1);

namespace App\Dto;

use App\Dto\TmdbMovieDetailsDto;

use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;

class DetailsDto
{
    public function __construct(
        public readonly bool $isSerial,
        public readonly string $name,
        /**
         * @var array<TranslationDto>
         */
        #[OA\Property(
            type: 'array',
            items: new OA\Items(ref: new Model(type: TranslationDto::class)),
        )]
        public readonly array $translators,
        public readonly ?string $poster,
        public readonly string $description,
        public readonly ?string $originalName,
        public readonly ?int $year = null,
        public readonly ?string $imdbId = null,
        public ?TmdbMovieDetailsDto $tmdb = null,
    ) {}
}
