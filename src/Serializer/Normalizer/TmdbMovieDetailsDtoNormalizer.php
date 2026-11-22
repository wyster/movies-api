<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use App\Dto\TmdbMovieDetailsDto;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class TmdbMovieDetailsDtoNormalizer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private readonly NormalizerInterface $objectNormalizer,
    ) {}

    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->objectNormalizer->normalize($object, $format, $context);
        \assert($object instanceof TmdbMovieDetailsDto);

        foreach (['posterPath', 'backdropPath'] as $field) {
            $path = $data[$field] ?? null;
            if (is_string($path) && '' !== trim($path)) {
                $data[$field] = 'https://image.tmdb.org/t/p/w500/' . ltrim($path, '/');
            }
        }

        return $data;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof TmdbMovieDetailsDto;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [TmdbMovieDetailsDto::class => true];
    }
}
