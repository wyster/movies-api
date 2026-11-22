<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\TmdbMovieDetailsDto;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class TmdbService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly CacheInterface $cache,
        #[Autowire(env: 'TMDB_API_KEY')]
        private readonly ?string $apiKey = null,
    ) {}

    public function findByImdbId(string $imdbId, string $language = 'en-US'): ?TmdbMovieDetailsDto
    {
        if ('' === trim((string) $this->apiKey) || !preg_match('/^tt\d+$/', $imdbId)) {
            return null;
        }

        return $this->cache->get('tmdb_find_' . hash('sha256', $language . ':' . $imdbId), function (ItemInterface $item) use ($imdbId, $language): ?TmdbMovieDetailsDto {
            $item->expiresAfter(86400);

            return $this->findByImdbIdWithoutCache($imdbId, $language);
        });
    }

    private function findByImdbIdWithoutCache(string $imdbId, string $language): ?TmdbMovieDetailsDto
    {

        $response = $this->httpClient->request(Request::METHOD_GET, 'https://api.themoviedb.org/3/find/' . rawurlencode($imdbId), [
            'query' => [
                'api_key' => $this->apiKey,
                'external_source' => 'imdb_id',
                'language' => $language,
            ],
        ]);

        $data = $response->toArray();

        return array_values(array_filter(array_map(static function (array $movie): ?TmdbMovieDetailsDto {
            if (!isset($movie['id'], $movie['title'], $movie['original_title'])) {
                return null;
            }

            return new TmdbMovieDetailsDto(
                $movie['id'],
                $movie['title'],
                $movie['original_title'],
                $movie['overview'] ?? null,
                $movie['release_date'] ?? null,
                $movie['poster_path'] ?? null,
                $movie['backdrop_path'] ?? null,
                (float) ($movie['vote_average'] ?? 0),
                [],
            );
        }, $data['movie_results'] ?? [])))[0];
    }

    public function getMovieDetails(int $movieId, string $language = 'en-US'): ?TmdbMovieDetailsDto
    {
        if ('' === trim((string) $this->apiKey) || $movieId < 1) {
            return null;
        }

        $response = $this->httpClient->request(Request::METHOD_GET, 'https://api.themoviedb.org/3/movie/' . $movieId, [
            'query' => [
                'api_key' => $this->apiKey,
                'language' => $language,
            ],
        ]);

        /** @var array{id?: int, title?: string, original_title?: string, overview?: string, release_date?: string, poster_path?: string, backdrop_path?: string, vote_average?: float|int, genres?: list<array{name?: string}>} $data */
        $data = $response->toArray();
        if (!isset($data['id'], $data['title'], $data['original_title'])) {
            return null;
        }

        return new TmdbMovieDetailsDto(
            $data['id'],
            $data['title'],
            $data['original_title'],
            $data['overview'] ?? null,
            $data['release_date'] ?? null,
            $data['poster_path'] ?? null,
            $data['backdrop_path'] ?? null,
            (float) ($data['vote_average'] ?? 0),
            array_values(array_filter(array_map(static fn(array $genre): ?string => $genre['name'] ?? null, $data['genres'] ?? []))),
        );
    }

    /** @return list<TmdbMovieDetailsDto> */
    public function searchMovies(string $query, string $language = 'en-US', int $page = 1): array
    {
        if ('' === trim((string) $this->apiKey) || '' === trim($query) || $page < 1) {
            return [];
        }

        $response = $this->httpClient->request(Request::METHOD_GET, 'https://api.themoviedb.org/3/search/movie', [
            'query' => [
                'api_key' => $this->apiKey,
                'query' => $query,
                'language' => $language,
                'page' => $page,
            ],
        ]);

        /** @var array{results?: list<array{id?: int, title?: string, original_title?: string, overview?: string, release_date?: string, poster_path?: string, backdrop_path?: string, vote_average?: float|int, genre_ids?: list<int>}>} $data */
        $data = $response->toArray();

        return array_values(array_filter(array_map(static function (array $movie): ?TmdbMovieDetailsDto {
            if (!isset($movie['id'], $movie['title'], $movie['original_title'])) {
                return null;
            }

            return new TmdbMovieDetailsDto(
                $movie['id'],
                $movie['title'],
                $movie['original_title'],
                $movie['overview'] ?? null,
                $movie['release_date'] ?? null,
                $movie['poster_path'] ?? null,
                $movie['backdrop_path'] ?? null,
                (float) ($movie['vote_average'] ?? 0),
                [],
            );
        }, $data['results'] ?? [])));
    }
}
