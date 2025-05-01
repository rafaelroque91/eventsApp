<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use App\Application\Service\AuthService;

class ApiException extends \RuntimeException {}

class ApiClient
{
    private ClientInterface $httpClient;
    private AuthService $authService;

    public function __construct(ClientInterface $httpClient, AuthService $authService)
    {
        $this->httpClient = $httpClient;
        $this->authService = $authService;
    }

    public function get(string $endpoint): array
    {
        return $this->request('GET', $endpoint);
    }

    public function post(string $endpoint, array $data): array
    {
        return $this->request('POST', $endpoint, $data);
    }

    private function request(string $method, string $endpoint, array $body = []): array
    {
        try {
            $token = $this->authService->getBearerToken();

            $headers = [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ];

            $request = new Request(
                $method,
                API_BASE_URL .$endpoint,
                $headers,
                json_encode($body));

//            var_dump( [$method,
//                API_BASE_URL .$endpoint,
//                $headers,
//                json_encode($body)]);
//            exit;

            $response = $this->httpClient->send($request);

            return $this->decodeResponse($response);

        } catch (RequestException $e) {
            $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 0;
            $responseBody = $e->hasResponse() ? (string) $e->getResponse()->getBody() : 'No response body';

            // Log the detailed error
            error_log(sprintf(
                "API Request Failed: %s %s | Status: %d | Response: %s | Exception: %s",
                $method,
                $endpoint,
                $statusCode,
                $responseBody,
                $e->getMessage()
            ));

            // Re-throw a more specific exception
            throw new ApiException(
                "API request failed for {$method} {$endpoint}. Status: {$statusCode}. Check logs for details.",
                $statusCode, // Use HTTP status code if available
                $e
            );
        } catch (\Throwable $e) {
            var_dump('exception2',$e->getMessage());
            exit;
            // Catch other potential errors (e.g., auth errors, decoding errors)
            error_log("General API Client Error: " . $e->getMessage());
            throw new ApiException("An unexpected error occurred during the API request.", 0, $e);
        }
    }

    private function decodeResponse(ResponseInterface $response): array
    {
        $body = $response->getBody()->getContents();

        if (empty($body)) {
            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                return [];
            } else {
                throw new ApiException('API returned empty body with status: ' . $response->getStatusCode());
            }
        }

        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ApiException('Failed to decode JSON response from API: ' . json_last_error_msg() . " | Body: " . $body);
        }
        return $data;
    }
}
