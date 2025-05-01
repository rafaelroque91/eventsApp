<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\Auth\DTO\TokenDto;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

class AuthService
{
    private ClientInterface $httpClient;
    private TokenCacheInterface $tokenCache;
    private string $authUrl;
    private string $clientId;
    private string $clientSecret;

    public function __construct(
        ClientInterface $httpClient,
        TokenCacheInterface $tokenCache,
        string $authUrl,
        string $clientId,
        string $clientSecret
    ) {
        $this->httpClient = $httpClient;
        $this->tokenCache = $tokenCache;
        $this->authUrl = $authUrl;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    public function getBearerToken(): string
    {
        //check redis
        $cachedToken = $this->tokenCache->getToken();
        if ($cachedToken !== null) {
            return $cachedToken;
        }

        try {
            $response = $this->httpClient->request('POST', $this->authUrl, [
                'json' => [
                    'clientId' => $this->clientId,
                    'clientSecret' => $this->clientSecret,
                ],
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json', // Common for OAuth
                ]
            ]);

            $data = $this->getTokenFromResponse($response);

            if (!isset($data['access_token'])) {
                throw new \RuntimeException('API did not return access_token in auth response.');
            }

            $token = $data['access_token'];
            $this->tokenCache->saveToken($token, (int)$data['expires_in']);
            return $token;

        } catch (RequestException $e) {
            $message = "Failed to fetch API token: " . $e->getMessage();
            if ($e->hasResponse()) {
                $message .= " | Response: " . $e->getResponse()->getBody();
            }
            error_log($message); // Log the error
            // Wrap Guzzle exception in a domain/application specific one
            throw new \App\Infrastructure\Http\ApiException("Authentication failed. Could not retrieve API token.", $e->getCode(), $e);
        } catch (\Throwable $e) {
            error_log("Error processing auth response: " . $e->getMessage());
            throw new \App\Infrastructure\Http\ApiException("Error processing authentication response.", $e->getCode(), $e);
        }
    }

    private function getTokenFromResponse(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Failed to decode JSON response from API: ' . json_last_error_msg() . " | Body: " . $body);
        }
        return $data;
    }
}