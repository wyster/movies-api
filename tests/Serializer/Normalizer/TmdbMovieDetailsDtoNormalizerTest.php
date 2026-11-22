<?php

declare(strict_types=1);

namespace App\Tests\Serializer\Normalizer;

use App\Dto\TmdbMovieDetailsDto;
use App\Serializer\Normalizer\TmdbMovieDetailsDtoNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class TmdbMovieDetailsDtoNormalizerTest extends TestCase
{
    public function testNormalizesImagePathsToFullUrls(): void
    {
        $normalizer = new TmdbMovieDetailsDtoNormalizer(new Serializer([new ObjectNormalizer()]));
        $dto = new TmdbMovieDetailsDto(
            123,
            'Example',
            'Original Example',
            null,
            null,
            '/poster.jpg',
            'backdrop.jpg',
            7.5,
            [],
        );

        $data = $normalizer->normalize($dto);

        static::assertSame('https://image.tmdb.org/t/p/w500/poster.jpg', $data['posterPath']);
        static::assertSame('https://image.tmdb.org/t/p/w500/backdrop.jpg', $data['backdropPath']);
    }

    public function testPreservesNullAndEmptyImagePaths(): void
    {
        $normalizer = new TmdbMovieDetailsDtoNormalizer(new Serializer([new ObjectNormalizer()]));
        $dto = new TmdbMovieDetailsDto(
            123,
            'Example',
            'Original Example',
            null,
            null,
            null,
            '',
            7.5,
            [],
        );

        $data = $normalizer->normalize($dto);

        static::assertNull($data['posterPath']);
        static::assertSame('', $data['backdropPath']);
    }
}
