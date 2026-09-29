<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\TranslateService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class TranslateServiceTest extends TestCase
{
    #[DataProvider('translationBypassDataProvider')]
    public function testTranslateReturnsOriginalTextWithoutMakingARequest(string $text, ?string $apiKey): void
    {
        $httpClient = new MockHttpClient(static function (): never {
            self::fail('The translation API should not be called.');
        });

        self::assertSame($text, (new TranslateService($httpClient, $apiKey))->translate($text));
    }

    /**
     * @return iterable<array{string, string|null}>
     */
    public static function translationBypassDataProvider(): iterable
    {
        yield ['', 'api-key'];
        yield ['   ', 'api-key'];
        yield ['Text', null];
        yield ['Text', ''];
        yield ['Text', '   '];
    }

    public function testTranslateSendsRequestAndDecodesHtmlEntities(): void
    {
        $request = [];
        $httpClient = new MockHttpClient(static function (string $method, string $url, array $options) use (&$request): ResponseInterface {
            $request = [$method, $url, $options];

            return new MockResponse('{"data":{"translations":[{"translatedText":"Привіт світе"}]}}');
        });

        self::assertSame('Привіт світе', (new TranslateService($httpClient, 'api-key'))->translate('Hello world'));
        self::assertSame('POST', $request[0]);
        self::assertSame('https://translation.googleapis.com/language/translate/v2?key=api-key', $request[1]);
        self::assertSame(['key' => 'api-key'], $request[2]['query']);
        self::assertSame([
            'q' => 'Hello world',
            'target' => 'uk',
            'format' => 'text',
        ], json_decode($request[2]['body'], true, flags: JSON_THROW_ON_ERROR));
    }

    #[DataProvider('translationFallbackDataProvider')]
    public function testTranslateReturnsOriginalTextWhenResponseCannotBeUsed(MockResponse $response): void
    {
        $httpClient = new MockHttpClient($response);

        self::assertSame('Hello', (new TranslateService($httpClient, 'api-key'))->translate('Hello'));
    }

    /**
     * @return iterable<array{MockResponse}>
     */
    public static function translationFallbackDataProvider(): iterable
    {
        yield [new MockResponse('{"data":{}}')];
        yield [new MockResponse('{invalid json}')];
        yield [new MockResponse('Service unavailable', ['http_code' => 500])];
    }
}
