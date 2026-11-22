<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\TmdbService;
use App\Dto\TmdbMovieDetailsDto;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class TmdbServiceTest extends TestCase
{
    #[DataProvider('findResultDataProvider')]
    public function testFindByImdbIdReturnsFirstMovieDto(string $responseBody, int $expectedId): void
    {
        $client = new MockHttpClient(static function (string $method, string $url, array $options) use ($responseBody): MockResponse {
            static::assertSame('GET', $method);
            static::assertSame('https://api.themoviedb.org/3/find/tt1234567?api_key=test-key&external_source=imdb_id&language=uk', $url);

            return new MockResponse($responseBody);
        });

        $service = new TmdbService($client, new ArrayAdapter(), 'test-key');

        $result = $service->findByImdbId('tt1234567');

        static::assertInstanceOf(TmdbMovieDetailsDto::class, $result);
        static::assertSame($expectedId, $result->id);
        static::assertSame('Example', $result->title);
        static::assertSame('Original Example', $result->originalTitle);
    }

    /** @return iterable<string, array{string, int}> */
    public static function findResultDataProvider(): iterable
    {
        $movie = ['id' => 11, 'title' => 'Example', 'original_title' => 'Original Example'];
        yield 'movie' => [json_encode(['movie_results' => [$movie]], JSON_THROW_ON_ERROR), 11];
        yield 'movie preferred over TV show' => [json_encode(['movie_results' => [$movie], 'tv_results' => [['id' => 22]]], JSON_THROW_ON_ERROR), 11];
    }

    public function testFindByImdbIdReturnsNullWhenNoResultsExist(): void
    {
        $service = new TmdbService(new MockHttpClient(new MockResponse('{"movie_results":[]}')), new ArrayAdapter(), 'test-key');

        static::assertNull($service->findByImdbId('tt1234567'));
    }

    public function testFindByImdbIdCachesResponse(): void
    {
        $requestCount = 0;
        $client = new MockHttpClient(static function () use (&$requestCount): MockResponse {
            ++$requestCount;

            return new MockResponse('{"movie_results":[{"id":11,"title":"Example","original_title":"Example"}]}');
        });
        $service = new TmdbService($client, new ArrayAdapter(), 'test-key');

        $first = $service->findByImdbId('tt1234567', 'uk-UA');
        $second = $service->findByImdbId('tt1234567', 'uk-UA');

        static::assertInstanceOf(TmdbMovieDetailsDto::class, $first);
        static::assertSame($first, $second);
        static::assertSame(1, $requestCount);
    }

    public function testFindByImdbIdDoesNotRequestWithInvalidId(): void
    {
        $client = new MockHttpClient(static function (): never {
            self::fail('No HTTP request should be made for an invalid IMDb ID.');
        });

        static::assertNull((new TmdbService($client, new ArrayAdapter(), 'test-key'))->findByImdbId('not-an-imdb-id'));
    }

    public function testFindByImdbIdDoesNotRequestWithoutApiKey(): void
    {
        $client = new MockHttpClient(static function (): never {
            self::fail('No HTTP request should be made without an API key.');
        });

        static::assertNull((new TmdbService($client, new ArrayAdapter()))->findByImdbId('tt1234567'));
    }

    public function testGetMovieDetailsMapsResponse(): void
    {
        $client = new MockHttpClient(static function (string $method, string $url): MockResponse {
            static::assertSame('GET', $method);
            static::assertSame('https://api.themoviedb.org/3/movie/123?api_key=test-key&language=uk-UA', $url);

            return new MockResponse(json_encode([
                'id' => 123,
                'title' => 'Example',
                'original_title' => 'Original Example',
                'overview' => 'A synopsis.',
                'release_date' => '2024-05-06',
                'poster_path' => '/poster.jpg',
                'backdrop_path' => '/backdrop.jpg',
                'vote_average' => 7.5,
                'genres' => [['id' => 1, 'name' => 'Drama'], ['id' => 2, 'name' => 'Mystery']],
            ], JSON_THROW_ON_ERROR));
        });

        $result = (new TmdbService($client, new ArrayAdapter(), 'test-key'))->getMovieDetails(123, 'uk-UA');

        static::assertInstanceOf(TmdbMovieDetailsDto::class, $result);
        static::assertSame(123, $result->id);
        static::assertSame('Example', $result->title);
        static::assertSame('Original Example', $result->originalTitle);
        static::assertSame('A synopsis.', $result->overview);
        static::assertSame('2024-05-06', $result->releaseDate);
        static::assertSame('/poster.jpg', $result->posterPath);
        static::assertSame('/backdrop.jpg', $result->backdropPath);
        static::assertSame(7.5, $result->voteAverage);
        static::assertSame(['Drama', 'Mystery'], $result->genres);
    }

    public function testGetMovieDetailsReturnsNullForIncompleteResponse(): void
    {
        $client = new MockHttpClient(new MockResponse('{"id":123,"title":"Example"}'));

        static::assertNull((new TmdbService($client, new ArrayAdapter(), 'test-key'))->getMovieDetails(123));
    }

    public function testGetMovieDetailsDoesNotRequestForInvalidId(): void
    {
        $client = new MockHttpClient(static function (): never {
            self::fail('No HTTP request should be made for an invalid movie ID.');
        });

        static::assertNull((new TmdbService($client, new ArrayAdapter(), 'test-key'))->getMovieDetails(0));
    }

    public function testSearchMoviesMapsResults(): void
    {
        $client = new MockHttpClient(static function (string $method, string $url): MockResponse {
            static::assertSame('GET', $method);
            static::assertSame('https://api.themoviedb.org/3/search/movie?api_key=test-key&query=Futurama&language=uk-UA&page=2', $url);

            return new MockResponse('{"results":[{"id":123,"title":"Футурама","original_title":"Futurama","overview":"Overview","release_date":"1999-03-28","poster_path":"/poster.jpg","backdrop_path":"/backdrop.jpg","vote_average":8.1,"genre_ids":[16,35]},{"id":456,"title":"Incomplete"}]}');
        });

        $results = (new TmdbService($client, new ArrayAdapter(), 'test-key'))->searchMovies('Futurama', 'uk-UA', 2);

        static::assertCount(1, $results);
        static::assertInstanceOf(TmdbMovieDetailsDto::class, $results[0]);
        static::assertSame(123, $results[0]->id);
        static::assertSame('Футурама', $results[0]->title);
        static::assertSame('Futurama', $results[0]->originalTitle);
        static::assertSame('Overview', $results[0]->overview);
        static::assertSame('1999-03-28', $results[0]->releaseDate);
        static::assertSame('/poster.jpg', $results[0]->posterPath);
        static::assertSame('/backdrop.jpg', $results[0]->backdropPath);
        static::assertSame(8.1, $results[0]->voteAverage);
        static::assertSame([], $results[0]->genres);
    }

    public function testSearchMoviesSkipsIncompleteResults(): void
    {
        $client = new MockHttpClient(new MockResponse('{"results":[{"id":123,"title":"Example"}]}'));

        static::assertSame([], (new TmdbService($client, new ArrayAdapter(), 'test-key'))->searchMovies('Example'));
    }

    public function testSearchMoviesDoesNotRequestEmptyQuery(): void
    {
        $client = new MockHttpClient(static function (): never {
            self::fail('No HTTP request should be made for an empty query.');
        });

        static::assertSame([], (new TmdbService($client, new ArrayAdapter(), 'test-key'))->searchMovies('  '));
    }
}
