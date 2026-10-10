<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ChromecastService
{
    private readonly HttpClientInterface $httpClient;

    public function __construct(
        HttpClientInterface $httpClient,
        #[Autowire(env: 'CHROMECAST_URL')]
        private readonly string $baseUrl,
    ) {
        $this->httpClient = $httpClient->withOptions([
            'timeout' => 10,
            'base_uri' => $this->baseUrl,
        ]);
    }

    /** @return array<mixed> */
    public function getDevices(?int $wait = null, ?string $interface = null): array
    {
        return $this->httpClient->request(Request::METHOD_GET, '/devices', [
            'query' => array_filter([
                'wait' => $wait,
                'iface' => $interface,
            ], static fn(mixed $value): bool => null !== $value),
        ])->toArray();
    }

    /** @return array<mixed> */
    public function connect(string $uuid, ?string $address = null, ?int $port = null): array
    {
        return $this->httpClient->request(Request::METHOD_POST, '/connect', [
            'query' => array_filter([
                'uuid' => $uuid,
                'addr' => $address,
                'port' => $port,
            ], static fn(mixed $value): bool => null !== $value),
        ])->toArray();
    }

    /** @return array<mixed> */
    public function disconnect(string $uuid): array
    {
        return $this->httpClient->request(Request::METHOD_POST, '/disconnect', [
            'query' => ['uuid' => $uuid],
        ])->toArray();
    }

    /** @return array<mixed> */
    public function getStatus(string $uuid): array
    {
        return $this->httpClient->request(Request::METHOD_GET, '/status', [
            'query' => ['uuid' => $uuid],
        ])->toArray();
    }

    /** @return array<mixed> */
    public function getStatuses(): array
    {
        return $this->httpClient->request(Request::METHOD_GET, '/status-all')->toArray();
    }

    /** @return array<mixed> */
    public function pause(string $uuid): array
    {
        return $this->httpClient->request(Request::METHOD_POST, '/pause', [
            'query' => ['uuid' => $uuid],
        ])->toArray();
    }

    /** @return array<mixed> */
    public function unpause(string $uuid): array
    {
        return $this->httpClient->request(Request::METHOD_POST, '/unpause', [
            'query' => ['uuid' => $uuid],
        ])->toArray();
    }

    /** @return array<mixed> */
    public function stop(string $uuid): array
    {
        return $this->httpClient->request(Request::METHOD_POST, '/stop', [
            'query' => ['uuid' => $uuid],
        ])->toArray();
    }

    /** @return array<mixed> */
    public function seek(string $uuid, float $seconds): array
    {
        return $this->httpClient->request(Request::METHOD_POST, '/seek-to', [
            'query' => ['uuid' => $uuid, 'seconds' => $seconds],
        ])->toArray();
    }

    /** @return array<mixed> */
    public function setVolume(string $uuid, float $volume): array
    {
        if ($volume < 0.0 || $volume > 1.0) {
            throw new \InvalidArgumentException('Volume must be between 0 and 1.');
        }

        return $this->httpClient->request(Request::METHOD_POST, '/volume', [
            'query' => ['uuid' => $uuid, 'volume' => $volume],
        ])->toArray();
    }

    /** @return array<mixed> */
    public function load(string $uuid, string $mediaUrl, ?string $contentType = null, ?int $startTime = null): array
    {
        return $this->httpClient->request(Request::METHOD_POST, '/load', [
            'query' => array_filter([
                'uuid' => $uuid,
                'path' => $mediaUrl,
                'content_type' => $contentType,
                'start_time' => $startTime,
            ], static fn(mixed $value): bool => null !== $value),
        ])->toArray();
    }
}
