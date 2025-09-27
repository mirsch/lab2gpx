<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\Dto\Coordinates;
use App\Model\Exception\BadGatewayHttpException;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use function array_merge;
use function assert;
use function ceil;
use function is_array;
use function is_string;
use function json_decode;
use function json_encode;
use function sprintf;
use function stripos;

class AdventureLabApiClient
{
    private Client $client;

    public function __construct(private readonly string $consumerKey)
    {
    }

    private function getClient(): Client
    {
        if (isset($this->client)) {
            return $this->client;
        }

        $config = [
            'base_uri' => 'https://labs-api.geocaching.com',
            'headers' => [
                'User-Agent' => 'Adventures/1.3.4 (2408) (ios/14.6)',
                'Accept' => 'application/json',
                'X-Consumer-Key' => $this->consumerKey,
            ],
            'http_errors' => false,
        ];

        $this->client = new Client($config);

        return $this->client;
    }

    /** @return array<mixed>|string */
    private function decodeResponse(ResponseInterface $response): array|string
    {
        if ($response->getStatusCode() === 404) {
            throw new NotFoundHttpException(sprintf('Got status %d from labs-api', $response->getStatusCode()));
        }

        if ($response->getStatusCode() !== 200) {
            $code = 0;
            // the (strange) response is:
            // {"Message":"Could not adventures from AdventuresApi. Adventure 9ff5ceb0-c58b-4183-899a-1fbb1bd6c2c0 with player "}
            if (stripos($response->getBody()->getContents(), 'Could not adventures from AdventuresApi') !== false) {
                $code = BadGatewayHttpException::POSSIBLE_ARCHIVED;
            }

            throw new BadGatewayHttpException(sprintf('Got status %d from labs-api', $response->getStatusCode()), $code);
        }

        $content = $response->getBody()->getContents();
        if (! $content) {
            throw new BadGatewayHttpException('Empty response from labs-api');
        }

        $decoded = json_decode($content, true);
        if (! $decoded) {
            throw new BadGatewayHttpException('Json decoding of response from labs-api failed');
        }

        return $decoded;
    }

    /**
     * @param array<mixed>|string $data
     *
     * @return array<mixed>|string
     */
    private function post(string $url, array|string $data): array|string
    {
        $response = $this->getClient()->post($url, [
            'body' => json_encode($data),
            'headers' => ['Content-Type' => 'application/json'],
        ]);

        return $this->decodeResponse($response);
    }

    /** @return array<mixed> */
    private function get(string $url): array
    {
        $response = $this->getClient()->get($url);

        return (array) $this->decodeResponse($response);
    }

    public function getAdventureIdBySmartLink(string $link): string
    {
        $result = $this->post('/Api/Adventures/GetAdventureIdBySmartLink', $link);
        assert(is_string($result));

        return $result;
    }

    /** @return array<mixed> */
    public function getAdventureById(string $id, string|null $userGuid): array
    {
        return $this->get('Api/Adventures/' . $id . ($userGuid ? '?callerGuid=' . $userGuid : ''));
    }

    /**
     * @param string[] $completionStatuses
     *
     * @return array<mixed>
     */
    public function searchV4(
        Coordinates $coordinates,
        float $radius,
        array $completionStatuses,
        string|null $userGuid,
        int $skip = 0,
        int $take = 300,
    ): array {
        $data = [
            'Origin' => ['Latitude' => $coordinates->lat, 'Longitude' => $coordinates->lon],
            'RadiusInMeters' => ceil($radius * 1000),
            'skip' => $skip,
            'take' => $take,
        ];
        if ($userGuid) {
            $data = array_merge($data, [
                'CallingUserPublicGuid' => $userGuid,
                'CompletionStatuses' => $completionStatuses,
            ]);
        }

        $result = $this->post('/Api/Adventures/SearchV4', $data);
        assert(is_array($result));

        return $result;
    }
}
