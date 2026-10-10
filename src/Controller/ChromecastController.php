<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\ChromecastService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Chromecast')]
final class ChromecastController extends AbstractController
{
    #[OA\Get(
        summary: 'Discover Chromecast devices',
        responses: [new OA\Response(response: 200, description: 'Discovered devices', content: new OA\JsonContent(type: 'array', items: new OA\Items(type: 'object', additionalProperties: true)))],
    )]
    #[Route('/chromecast/devices', methods: [Request::METHOD_GET])]
    public function devices(
        ChromecastService $chromecastService,
        #[MapQueryParameter]
        ?int $wait = null,
        #[MapQueryParameter]
        ?string $interface = null,
    ): JsonResponse {
        return $this->json($chromecastService->getDevices($wait, $interface));
    }

    #[OA\Post(
        summary: 'Connect to a Chromecast device',
        responses: [new OA\Response(response: 200, description: 'Connection result', content: new OA\JsonContent(type: 'object', additionalProperties: true))],
    )]
    #[Route('/chromecast/connect', methods: [Request::METHOD_POST])]
    public function connect(
        #[MapQueryParameter]
        string $uuid,
        ChromecastService $chromecastService,
        #[MapQueryParameter]
        ?string $address = null,
        #[MapQueryParameter]
        ?int $port = null,
    ): JsonResponse {
        return $this->json($chromecastService->connect($uuid, $address, $port));
    }

    #[OA\Post(
        summary: 'Disconnect from a Chromecast device',
        responses: [new OA\Response(response: 200, description: 'Disconnection result', content: new OA\JsonContent(type: 'object', additionalProperties: true))],
    )]
    #[Route('/chromecast/disconnect', methods: [Request::METHOD_POST])]
    public function disconnect(#[MapQueryParameter] string $uuid, ChromecastService $chromecastService): JsonResponse
    {
        return $this->json($chromecastService->disconnect($uuid));
    }

    #[OA\Get(
        summary: 'Get a Chromecast device status',
        responses: [new OA\Response(response: 200, description: 'Device status', content: new OA\JsonContent(type: 'object', additionalProperties: true))],
    )]
    #[Route('/chromecast/status', methods: [Request::METHOD_GET])]
    public function status(#[MapQueryParameter] string $uuid, ChromecastService $chromecastService): JsonResponse
    {
        return $this->json($chromecastService->getStatus($uuid));
    }

    #[OA\Get(
        summary: 'Get statuses for all connected Chromecast devices',
        responses: [new OA\Response(response: 200, description: 'Device statuses', content: new OA\JsonContent(type: 'array', items: new OA\Items(type: 'object', additionalProperties: true)))],
    )]
    #[Route('/chromecast/statuses', methods: [Request::METHOD_GET])]
    public function statuses(ChromecastService $chromecastService): JsonResponse
    {
        return $this->json($chromecastService->getStatuses());
    }

    #[OA\Post(
        summary: 'Pause playback on a Chromecast device',
        responses: [new OA\Response(response: 200, description: 'Pause result', content: new OA\JsonContent(type: 'object', additionalProperties: true))],
    )]
    #[Route('/chromecast/pause', methods: [Request::METHOD_POST])]
    public function pause(#[MapQueryParameter] string $uuid, ChromecastService $chromecastService): JsonResponse
    {
        return $this->json($chromecastService->pause($uuid));
    }

    #[OA\Post(
        summary: 'Resume playback on a Chromecast device',
        responses: [new OA\Response(response: 200, description: 'Resume result', content: new OA\JsonContent(type: 'object', additionalProperties: true))],
    )]
    #[Route('/chromecast/unpause', methods: [Request::METHOD_POST])]
    public function unpause(#[MapQueryParameter] string $uuid, ChromecastService $chromecastService): JsonResponse
    {
        return $this->json($chromecastService->unpause($uuid));
    }

    #[OA\Post(
        summary: 'Stop playback on a Chromecast device',
        responses: [new OA\Response(response: 200, description: 'Stop result', content: new OA\JsonContent(type: 'object', additionalProperties: true))],
    )]
    #[Route('/chromecast/stop', methods: [Request::METHOD_POST])]
    public function stop(#[MapQueryParameter] string $uuid, ChromecastService $chromecastService): JsonResponse
    {
        return $this->json($chromecastService->stop($uuid));
    }

    #[OA\Post(
        summary: 'Seek playback to a position in seconds',
        responses: [new OA\Response(response: 200, description: 'Seek result', content: new OA\JsonContent(type: 'object', additionalProperties: true))],
    )]
    #[Route('/chromecast/seek', methods: [Request::METHOD_POST])]
    public function seek(
        #[MapQueryParameter]
        string $uuid,
        #[MapQueryParameter]
        float $seconds,
        ChromecastService $chromecastService,
    ): JsonResponse {
        return $this->json($chromecastService->seek($uuid, $seconds));
    }

    #[OA\Post(
        summary: 'Set Chromecast device volume',
        responses: [new OA\Response(response: 200, description: 'Volume result', content: new OA\JsonContent(type: 'object', additionalProperties: true))],
    )]
    #[Route('/chromecast/volume', methods: [Request::METHOD_POST])]
    public function volume(
        #[MapQueryParameter]
        string $uuid,
        #[MapQueryParameter]
        float $volume,
        ChromecastService $chromecastService,
    ): JsonResponse {
        return $this->json($chromecastService->setVolume($uuid, $volume));
    }

    #[OA\Post(
        summary: 'Load media on a Chromecast device',
        responses: [new OA\Response(response: 200, description: 'Load result', content: new OA\JsonContent(type: 'object', additionalProperties: true))],
    )]
    #[Route('/chromecast/load', methods: [Request::METHOD_POST])]
    public function load(
        #[MapQueryParameter]
        string $uuid,
        #[MapQueryParameter]
        string $url,
        ChromecastService $chromecastService,
        #[MapQueryParameter]
        ?string $contentType = null,
        #[MapQueryParameter]
        ?int $startTime = null,
    ): JsonResponse {
        return $this->json($chromecastService->load($uuid, $url, $contentType, $startTime));
    }
}
