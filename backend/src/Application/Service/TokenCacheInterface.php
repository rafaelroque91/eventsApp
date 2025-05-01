<?php

declare(strict_types=1);

namespace App\Application\Service;

interface TokenCacheInterface
{
    public function getToken(): ?string;
    public function saveToken(string $token, ?int $expiresInSeconds = null): void;
    public function clearToken(): void;
}