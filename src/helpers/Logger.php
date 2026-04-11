<?php

namespace BrindlyDotArt\BunnyStorage\helpers;

use Craft;

class Logger
{
    public static function info(string $message): void
    {
        Craft::info($message, 'bunny-storage');
    }

    public static function warning(string $message): void
    {
        Craft::warning($message, 'bunny-storage');
    }

    public static function error(string $message): void
    {
        Craft::error($message, 'bunny-storage');
    }

    // -------------------------------------------------------------------------
    // CDN
    // -------------------------------------------------------------------------

    public static function cdnPurgeSkippedNoApiKey(): void
    {
        static::warning('CDN purge skipped — no account API key set.');
    }

    public static function cdnPurgeSkippedNoRootUrl(): void
    {
        static::warning('CDN purge skipped — no root URL.');
    }

    public static function cdnPurging(string $url): void
    {
        static::info('CDN purging: ' . $url);
    }

    public static function cdnPurgeResponse(int $statusCode, string $url): void
    {
        static::info('CDN purge response ' . $statusCode . ' for: ' . $url);
    }

    public static function cdnPurgeFailed(string $url, string $reason): void
    {
        static::error('CDN purge failed for ' . $url . ': ' . $reason);
    }
}
