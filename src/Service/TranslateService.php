<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function Sentry\captureException;

final class TranslateService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        #[Autowire(env: 'GOOGLE_TRANSLATE_API_KEY')]
        private readonly ?string $apiKey = null,
    ) {}

    public function translate(string $text): string
    {
        if ('' === trim($text) || '' === trim((string) $this->apiKey)) {
            return $text;
        }

        try {
            $response = $this->httpClient->request(Request::METHOD_POST, 'https://translation.googleapis.com/language/translate/v2', [
                'query' => ['key' => $this->apiKey],
                'json' => [
                    'q' => $text,
                    'target' => 'uk',
                    'format' => 'text',
                ],
            ]);

            /** @var array{data?: array{translations?: array<int, array{translatedText?: string}>}} $data */
            $data = json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR);

            return html_entity_decode((string) ($data['data']['translations'][0]['translatedText'] ?? $text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        } catch (\Throwable $e) {
            captureException($e);

            return $text;
        }
    }
}
