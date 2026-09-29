<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use App\Dto\TranslationDto;
use App\Service\TranslateService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class TranslationDtoNormalizer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private readonly NormalizerInterface $objectNormalizer,
        private readonly TranslateService $translateService,
    ) {}

    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        /** @var array{id: int, title: string} $data */
        $data = $this->objectNormalizer->normalize($object, $format, $context);
        \assert($object instanceof TranslationDto);

        $data['title'] = $this->translateService->translate($object->title);

        return $data;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof TranslationDto;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [TranslationDto::class => true];
    }
}
