<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\Preview;

use Psr\Cache\CacheItemPoolInterface;

/**
 * Short-lived storage of unsaved page content. The random token is the only access control:
 * it is issued to an authenticated administrator and expires quickly.
 */
final class PreviewStore
{
    public const TTL = 1800;

    private const KEY_PREFIX = 'akawaka_cms_preview_';

    private const TOKEN_PATTERN = '/^[a-f0-9]{32}$/';

    public function __construct(private readonly CacheItemPoolInterface $cache)
    {
    }

    public function create(PagePreview $preview): string
    {
        $token = bin2hex(random_bytes(16));
        $this->save($token, $preview);

        return $token;
    }

    /** Replaces the content behind an existing token; returns false if the token is unknown or expired. */
    public function update(string $token, PagePreview $preview): bool
    {
        if (!$this->exists($token)) {
            return false;
        }

        $this->save($token, $preview);

        return true;
    }

    public function get(string $token): ?PagePreview
    {
        if (1 !== preg_match(self::TOKEN_PATTERN, $token)) {
            return null;
        }

        $item = $this->cache->getItem(self::KEY_PREFIX . $token);
        $data = $item->get();

        return $item->isHit() && \is_array($data) ? PagePreview::fromArray($data) : null;
    }

    private function exists(string $token): bool
    {
        return 1 === preg_match(self::TOKEN_PATTERN, $token) && $this->cache->hasItem(self::KEY_PREFIX . $token);
    }

    private function save(string $token, PagePreview $preview): void
    {
        $item = $this->cache->getItem(self::KEY_PREFIX . $token);
        $item->set($preview->toArray());
        $item->expiresAfter(self::TTL);
        $this->cache->save($item);
    }
}
