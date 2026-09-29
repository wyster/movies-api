<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use App\Dto\DetailsDto;
use App\Service\TranslateService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class DetailsDtoNormalizer implements NormalizerInterface
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
        \assert($object instanceof DetailsDto);

        $data['name'] = $this->translateService->translate($object->name);
        $data['description'] = $this->translateService->translate($object->description);

        return $data;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof DetailsDto;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [DetailsDto::class => true];
    }
}
