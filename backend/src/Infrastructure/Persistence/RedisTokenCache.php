<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use Redis;
use App\Application\Service\TokenCacheInterface;

class RedisTokenCache implements TokenCacheInterface
{
    private const int SAFETY_BUFFER = 60;

    public function __construct(
        private readonly Redis $redis,
        private readonly string $cacheKey)
    {
    }

    public function getToken(): ?string
    {
        $token = $this->redis->get($this->cacheKey);
        return $token === false ? null : $token;
    }

    public function saveToken(string $token, ?int $expiresInSeconds = null): void
    {
        $ttl = max(1, $expiresInSeconds - self::SAFETY_BUFFER);
        $this->redis->setex($this->cacheKey, $ttl, $token);
    }

    public function clearToken(): void
    {
        $this->redis->del($this->cacheKey);
    }
}