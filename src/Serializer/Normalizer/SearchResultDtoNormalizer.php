<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use App\Dto\SearchResultDto;
use App\Service\TranslateService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class SearchResultDtoNormalizer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private readonly NormalizerInterface $objectNormalizer,
        private readonly TranslateService $translateService,
    ) {}

    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->objectNormalizer->normalize($object, $format, $context);
        \assert($object instanceof SearchResultDto);

        $data['name'] = $this->translateService->translate($object->name);

        return $data;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof SearchResultDto;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [SearchResultDto::class => true];
    }
}
